# Teste de Migração de Dados da F360

## Instalação e Configuração
Iremos mostrar como configurar o ambiente no sistema operacional Windows.

Para facilitar as coisas, iremos utilizar o Laragon para criar o ambiente
para nós. Basta acessar o site <https://laragon.org/> baixar a aplicação
e instalar no computador.

Após fazer a instalação do Laragon, basta abri-lo e clicar sobre o botão
`Start` para iniciar os serviços do servidor Apache.

Contudo iremos utilizar o banco de dados MongoDB. Basta acesar o site
<https://www.mongodb.com/try/download/community> e baixar o instalador MSI
e seguir com os passos.

Agora temos que configurar o `driver` do MongoDB no PHP. Para isso basta
seguir os passos do site <https://docs.mongodb.com/drivers/php/>, que seria
baixar o arquivo `.dll` do PECL, versão TS, correspondente com o PHP utilizado
no Laragon, e colocar esse arquivo dentro da pasta `ext`. E depois adicionar
o seguinte código `extension=php_mongodb.dll` no final do arquivo `php.ini`.

Também vamos necessitar do Composer, que pode ser obtido através do
seguinte endereço:  `https://getcomposer.org/`.

Agora basta rodar o comando `git clone <url do repositório>` dentro da pasta
`C:\laragon\www`. Por fim devemos rodar `composer update` e `composer dumpautoload`.

Pronto. Aplicação configurada e pronta para uso!


## Solução para as etapas do teste
Rode todos os comandos dentro da pasta raiz do projeto.


### 1 Crie um robô que baixe um ou mais arquivos de CNPJ
Para baixar um arquivo de CNPJ, basta rodar o comando:<br>
`php source/Migracao/BotDownload.php <nome do arquivo>`

Para configurar de onde o arquivo será baixado, basta mudar o valor da variável
`$url_base`. O nome do arquivo a ser baixado está na variável `$file_name` e a
variável `$path_save` contém o local onde o arquivo será salvo.

Exemplo de uso: `php source/Migracao/BotDownload.php F.K03200$Z.D10510.MUNICCSV.zip`.


### 2 Extrair o arquivo zip
Para extrair o arquivo CNPJ, basta rodar o comando:<br>
`php source/Migracao/BotExtract <nome do arquivo>`

A variável `$path_file` diz onde está o arquivo `.zip`. Já a variável
`$file_name` fala qual é o nome do arquivo. E a variável `$path_extract`
informa onde o arquivo será extraído.

Exemplo de uso: `php source/Migracao/BotExtract.php DADOS_ABERTOS_CNPJ_01.zip`.


### 3 Ler o arquivo e fazer o parser
O código responsável pelo `parse` do arquivo é a classe `FileReader` do
`namespace` F360\Migracao\Pipeline. Um exemplo de uso da mesma pode ser
vista no endereço de teste <http://localhost/f360-teste-migracao/teste>.

Se você olhar bem, pode perceber que foi implementado um parse que faz
a busca dos dados na forma de blocos. O método `parse($register_start, $register_end)`
recebe duas variáveis para que você possa especificar em que registro começar
a coleta e em que registro você deseja parar. Visto que o arquivo contém muitos
registros.


### 4 Inserir os dados em um banco de dados MongoDB
Para fazer a inserção dos dados, basta rodar o comando:<br>
`php source/Migracao/EntityDatabase`

Como o arquivo é grande, pode demorar muito tempo para inserir os dados,
então para facilitar a inserção existem duas variáveis: a primeira controla
a quantidade de registros a serem inseridos por vez, que é `$block_size`,
e a segunda a quantidade de registros a serem inseridos, a `$block_quant`.

Caso a base já exista, o script não irá rodar. Será preciso deletar a base
primeiro antes, para depois rodar novamente o script para ele fazer a inserção.

Existem um total de `4686125` registros no arquivo `DADOS_ABERTOS_CNPJ_01.zip`.

Por algum motivo desconhecido alguns dados não consegui extrair. Eu tentei
olhar o arquivo com um editor HEX e parece que alguns campos não contém dados,
ou está com dados de outro campo. Como é só um arquivo de teste, eu segui a
especificação do layout para extrair os dados.


### 5 Crie uma API que retorne os dados
A API vai estar disponível no seguinte `endpoint` de acesso:<br>
`localhost/f360-teste-migracao/cnpj/<número do cnpj>`.

Exemplo de URL: <http://localhost/f360-teste-migracao/cnpj/00000000000191>.


## Próximos passos
- [ ] Tentar deixar o código mais modularizado (criação de uma interface para leitura de layouts).
- [ ] Extrair as variáveis que guardam os caminhos e URLs em um arquivo de configuração.