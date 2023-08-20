<?php
namespace Komodo\Interlace\Exceptions;

/*.+
|-----------------------------------------------------------------------------
| Komodo Routes
|-----------------------------------------------------------------------------
|
| Desenvolvido por: Jhonnata Paixão (Líder de Projeto)
| Iniciado em: 15/10/2022
| Arquivo: ResponseError.php
| Data da Criação Fri Jul 21 2023
| Copyright (c) 2023
|
|-----------------------------------------------------------------------------
|*/

use Exception;
use Komodo\Logger\Logger;

abstract class ExceptionHandle extends Exception
{
    public function __construct($message, $code, \Throwable $previous = null, Logger $logger = null)
    {
        $this->log($logger);
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    private function log(Logger $logger)
    {
        if (!$logger) {
            return;
        }

        $logger->error([
            'type' => get_called_class(),
            'code' => $this->code,
            'file' => $this->file,
            'line' => $this->line,
         ], $this->message);
    }
}
