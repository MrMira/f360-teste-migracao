<?php

namespace F360\Migracao\Pipeline;


/**
 * Realiza a leitura do arquivo e coleta os dados necessários.
 */
class FileReader
{
    private $path_full;

    private $handler;
    private $isOpen;

    private $mode;
    private $size;

    private $layouts;

    /**
     * Dado um caminho para o arquivo, atribui um manipulador
     * que pode ser utilizado para leitura do arquivo.
     *
     * @param string $path_full
     */
    public function __construct($path_full)
    {
        $this->path_full = $path_full;
        $this->isOpen = false;
        $this->mode = 'rb';
        $this->size = filesize($path_full);
        $this->pointer = 0;

        $this->register_quantity = $this->size / 1201;

        $this->define_layouts();
    }

    /**
     * Caso o arquivo não seja fechado de forma manual,
     * vamos ter que fechar o arquivo por nossa própria conta.
     */
    public function __destruct()
    {
        if ($this->isOpen) {
            $this->close();
        }
    }

    /**
     * Abre o arquivo.
     *
     * @return void
     */
    public function open()
    {
        $result = fopen(
            $this->path_full,
            $this->mode
        );

        // Fail fast baby!
        if ($result === false) {
            return;
        }

        $this->handler = $result;
        $this->isOpen = true;
    }

    /**
     * Retorna a quantidade de registros.
     *
     * @return int
     */
    public function get_register_quantity()
    {
        return $this->register_quantity;
    }



    /**
     * Verifica se o arquivo está aberto.
     *
     * @return boolean
     */
    public function is_open()
    {
        return $this->isOpen;
    }

    /**
     * Realiza a leitura e o parse dos dados.
     *
     * @param integer $register_start Número do primeiro registro a ser lido (inclusivo).
     * @param integer $register_end Último registro a ser lido (exclusivo).
     * 
     * @return void
     */
    public function parse($register_start = 0, $register_end = 10)
    {
        // 0. Inicializa o array que irá guardar os registros lidos.
        $register_block = [];

        if ($this->handler === null) {
            return;
        }

        // NOTE: Estamos assumindo que o arquivo é um binário, onde a codificação
        // utilizada para armazenar os dados foi ASCII. Espero estar certo!

        // Como o arquivo é grandinho, vamos ter que ler em pedaços menores
        // para não sobrecarregar a memória.

        // Lendo o layout do arquivo podemos ver que cada registro tem
        // exatamente 1200 bytes de tamanho. O que vai facilitar o parse.
        $HEADER_SIZE = 1200;
        $REG_SIZE = 1200;


        // 1. Se necessário, vamos pular no meio do arquivo.
        fseek($this->handler, ($REG_SIZE + 1) * $register_start);

        // 2. Vamos descontar o 'header' porque ele não é interessante.
        fread($this->handler, $HEADER_SIZE + 1);


        // 3. Agora vamos fazer a mágica de ler o arquivo.
        $register_step = 0;

        while (
            !feof($this->handler)
        ) {
            // Caso chegamos no fim, vamos retornar o que foi lido.
            if ($register_step === $register_end) {
                return $register_block;
            }

            // a. Move o ponteiro para o próximo registro.
            $content = fread($this->handler, $REG_SIZE + 1);

            if (empty($content)) {
                return $register_block;
            }

            // b. Agora vamos ler o registro selecionado.
            $extract = $this->extraction();

            $register_row = ''; // . variável debug para coleta da linha (registro)

            for ($pos = 1; $pos < $REG_SIZE; $pos++) {
                // Vamos usar os número do layout, que começam em 1.
                // Então vamos ter que deslocar um pouco para trás.
                $char = $content[$pos - 1];

                $register_row .= $char;

                //
                // PARSE DOS DADOS
                // Daria para abstrair mais ou melhor, mas por enquanto desse
                // modo que foi feito está suficiente para o que precisa ser feito.
                //

                // 1. Dados empresas (LAYOUT PRINCIPAL)

                // CNPJ
                if ($this->extract_in_range('LAYOUT PRINCIPAL', 'CNPJ', $pos)) {
                    $extract['LAYOUT PRINCIPAL']['CNPJ'] .= $char;
                }

                // IDENTIFICADOR MATRIZ/FILIAL 
                if ($this->extract_in_range('LAYOUT PRINCIPAL', 'IDENTIFICADOR MATRIZ/FILIAL', $pos)) {
                    $extract['LAYOUT PRINCIPAL']['IDENTIFICADOR MATRIZ/FILIAL'] .= $char;
                }

                // RAZÃO SOCIAL/NOME EMPRESARIAL
                if ($this->extract_in_range('LAYOUT PRINCIPAL', 'RAZÃO SOCIAL/NOME EMPRESARIAL', $pos)) {
                    $extract['LAYOUT PRINCIPAL']['RAZÃO SOCIAL/NOME EMPRESARIAL'] .= $char;
                }

                // NOME FANTASIA
                if ($this->extract_in_range('LAYOUT PRINCIPAL', 'NOME FANTASIA', $pos)) {
                    $extract['LAYOUT PRINCIPAL']['NOME FANTASIA'] .= $char;
                }
                // CAPITAL SOCIAL DA EMPRESA
                if ($this->extract_in_range('LAYOUT PRINCIPAL', 'CAPITAL SOCIAL DA EMPRESA', $pos)) {
                    $extract['LAYOUT PRINCIPAL']['CAPITAL SOCIAL DA EMPRESA'] .= $char;
                }

                // SITUAÇÃO CADASTRAL
                if ($this->extract_in_range('LAYOUT PRINCIPAL', 'SITUAÇÃO CADASTRAL', $pos)) {
                    $extract['LAYOUT PRINCIPAL']['SITUAÇÃO CADASTRAL'] .= $char;
                }

                // DATA SITUACAO CADASTRAL
                if ($this->extract_in_range('LAYOUT PRINCIPAL', 'DATA SITUACAO CADASTRAL', $pos)) {
                    $extract['LAYOUT PRINCIPAL']['DATA SITUACAO CADASTRAL'] .= $char;
                }

                // CEP
                if ($this->extract_in_range('LAYOUT PRINCIPAL', 'CEP', $pos)) {
                    $extract['LAYOUT PRINCIPAL']['CEP'] .= $char;
                }


                // 2. Sócios (LAYOUT SOCIOS)

                // IDENTIFICADOR DE SOCIO 
                if ($this->extract_in_range('LAYOUT SOCIOS', 'IDENTIFICADOR DE SOCIO', $pos)) {
                    $extract['LAYOUT SOCIOS']['IDENTIFICADOR DE SOCIO'] .= $char;
                }

                // NOME SOCIO (NO CASO PF) OU RAZÃO SOCIAL (NO CASO PJ) 
                if ($this->extract_in_range('LAYOUT SOCIOS', 'NOME SOCIO (NO CASO PF) OU RAZÃO SOCIAL (NO CASO PJ)', $pos)) {
                    $extract['LAYOUT SOCIOS']['NOME SOCIO (NO CASO PF) OU RAZÃO SOCIAL (NO CASO PJ)'] .= $char;
                }

                // CNPJ/CPF DO SÓCIO
                if ($this->extract_in_range('LAYOUT SOCIOS', 'CNPJ/CPF DO SÓCIO', $pos)) {
                    $extract['LAYOUT SOCIOS']['CNPJ/CPF DO SÓCIO'] .= $char;
                }
            }

            // Limpeza dos dados (e.g. remoção de espaços em branco).
            $extract['LAYOUT PRINCIPAL'] = $this->trim_extraction($extract['LAYOUT PRINCIPAL']);
            $extract['LAYOUT SOCIOS'] = $this->trim_extraction($extract['LAYOUT SOCIOS']);


            $register_block[] = $extract;


            $register_step++;
        } // ./ end of while loop
    }

    /**
     * Obtém em que formato os dados devem ser extraídos.
     *
     * @return array
     */

    private function extraction()
    {
        return [
            'LAYOUT PRINCIPAL' =>
            [
                'CNPJ' => null,
                'IDENTIFICADOR MATRIZ/FILIAL' => null,
                'RAZÃO SOCIAL/NOME EMPRESARIAL' => null,
                'NOME FANTASIA' => null,
                'CAPITAL SOCIAL DA EMPRESA' => null,
                'SITUAÇÃO CADASTRAL' => null,
                'DATA SITUACAO CADASTRAL' => null,
                'CEP' => null
            ],
            'LAYOUT SOCIOS' =>
            [
                'IDENTIFICADOR DE SOCIO' => null,
                'NOME SOCIO (NO CASO PF) OU RAZÃO SOCIAL (NO CASO PJ)' => null,
                'CNPJ/CPF DO SÓCIO' => null,
            ],
        ];
    }

    /**
     * Define o layout do arquivo.
     *
     * @return array
     */
    private function define_layouts()
    {
        $this->layouts = [
            'LAYOUT PRINCIPAL' =>
            [
                'CNPJ' =>
                [
                    'OFFSET' => 4,
                    'SIZE' => 14
                ],

                'IDENTIFICADOR MATRIZ/FILIAL' =>
                [
                    'OFFSET' => 18,
                    'SIZE' => 1
                ],

                'RAZÃO SOCIAL/NOME EMPRESARIAL' =>
                [
                    'OFFSET' => 19,
                    'SIZE' => 150
                ],

                'NOME FANTASIA' =>
                [
                    'OFFSET' => 169,
                    'SIZE' => 55
                ],

                'CAPITAL SOCIAL DA EMPRESA' =>
                [
                    'OFFSET' => 892,
                    'SIZE' => 14
                ],

                'SITUAÇÃO CADASTRAL' =>
                [
                    'OFFSET' => 224,
                    'SIZE' => 2
                ],

                'DATA SITUACAO CADASTRAL' =>
                [
                    'OFFSET' => 226,
                    'SIZE' => 8
                ],

                'CEP' =>
                [
                    'OFFSET' => 675,
                    'SIZE' => 8
                ],
            ],
            'LAYOUT SOCIOS' =>
            [
                'IDENTIFICADOR DE SOCIO' =>
                [
                    'OFFSET' => 18,
                    'SIZE' => 1
                ],
                'NOME SOCIO (NO CASO PF) OU RAZÃO SOCIAL (NO CASO PJ)' =>
                [
                    'OFFSET' => 19,
                    'SIZE' => 150
                ],
                'CNPJ/CPF DO SÓCIO' =>
                [
                    'OFFSET' => 169,
                    'SIZE' => 14
                ],
            ],
        ];
    }

    /**
     * Remove espaços em branco (' ') inicias e finais dos valores de um array.
     *
     * @param array $array
     * 
     * @return void
     */
    public function trim_extraction($array)
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return trim($value);
            }

            return $value;
        }, $array);
    }

    /**
     * Dado um layout e um campo, verifica se dada a posição no arquivo
     * que aponta para um carácter, se ele pertence ao campo deste layout.
     *
     * @param string $layout_name
     * @param string $field_name
     * 
     * @param integer $pos
     * 
     * @return void
     */
    public function extract_in_range($layout_name, $field_name, $pos)
    {
        $offset = $this->layouts[$layout_name][$field_name]['OFFSET'];
        $size   = $this->layouts[$layout_name][$field_name]['SIZE'];

        return $this->in_field_range($offset, $size, $pos);
    }

    /**
     * Dado um offset e um tamanho, verifica se a posição apontada no arquivo
     * (carácter em questão) está dentro do range definido pelo offset e
     * offset + tamanho (início e fim respectivamente).
     *
     * @param integer $pos
     * @param integer $offset
     * 
     * @param integer $size
     * 
     * @return void
     */
    private function in_field_range($offset, $size, $pos)
    {
        return $pos >= $offset && $pos < $offset + $size;
    }

    /**
     * Fecha o arquivo.
     *
     * @return void
     */
    public function close()
    {
        if ($this->handler === null) {
            return false;
        }

        $result = fclose($this->handler);

        if ($result === true) {
            $this->isOpen = false;
        }
    }
}
