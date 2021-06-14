<?php

namespace F360\Migracao\Pipeline;

/**
 * Permite fazer o download e um arquivo a partir de uma URL.
 */
class FileDownloaderFromURL
{
    private $url_base;
    private $file_name;
    private $path_save;

    /**
     * Construtor da classe.
     *
     * @param string $url_base Url para a 'pasta' onde se encontra o arquivo.
     * @param string $file_name Nome do arquivo dentro da pasta que a URL aponta.
     * @param string $path_save Caminho para onde o arquivo deve ser salvo.
     */
    public function __construct($url_base, $file_name, $path_save)
    {
        $this->url_base = $url_base;
        $this->file_name = $file_name;
        $this->path_save = $path_save;
    }


    /**
     * Realiza o download do arquivo.
     *
     * @return void
     */
    private function download_file()
    {
        $url_full = $this->url_base . $this->file_name;
        $path_full = $this->path_save . $this->file_name;

        $ch = curl_init($url_full);

        $fp = fopen($path_full, "wb");

        if ($fp === false) {
            return false;
        }

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $result = curl_exec($ch);

        curl_close($ch);


        fclose($fp);

        return $result;
    }


    /**
     * Caso não exista um arquivo da URL de mesmo nome na pasta local.
     * Faz o download do mesmo. Caso contrário não.
     *
     * @return void
     */
    public function download_if_not_exists()
    {
        $path_full = $this->path_save . $this->file_name;

        $existFile = file_exists($path_full);

        if (!$existFile) {
            return $this->download_file();
        }

        return false;
    }
}
