<?php

namespace F360\Migracao\Pipeline;


/**
 * Realiza a extração de um arquivo dentro de um diretório local.
 */
class FileExtractor
{
    /**
     * Objeto 'handler' para lidar com a extração.
     *
     * @var mixed
     */
    private $handler;

    private $path_raw;
    private $file_name;
    private $path_extract;

    public function __construct($path_raw, $file_name, $path_extract)
    {
        $this->path_raw = $path_raw;
        $this->file_name = $file_name;
        $this->path_extract = $path_extract;

        $this->handler = new \ZipArchive();
    }

    /**
     * Realiza a extração do arquivo.
     *
     * @return void
     */
    public function extract()
    {
        $path_full = $this->path_raw . '/' . $this->file_name;
        $isOpen =  $this->handler->open($path_full);
        
        $isExtracted = false;
        if($isOpen) {
            $isExtracted = $this->handler->extractTo($this->path_extract);
       }

       return $isExtracted;
    }
}