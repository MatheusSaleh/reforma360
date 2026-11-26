<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Exceptions;

use RuntimeException;

/**
 * O gerador de DANFSe recusou o XML (HTTP 4xx).
 */
final class DanfseRejeitadoException extends RuntimeException {}
