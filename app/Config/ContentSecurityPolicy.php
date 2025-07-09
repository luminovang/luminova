<?php 
/**
 * Luminova Framework ContentSecurityPolicy Configuration Class
 *
 * This class extends the Configuration and provides a simple, customizable way to generate and manage 
 * Content-Security-Policy (CSP) directives. It helps in enhancing the security of web applications by 
 * controlling what resources are allowed to load.
 * 
 * The CSP directives are initialized in the `onCreate` method and can be further modified to suit your specific needs. 
 * You can easily add, remove, or clear CSP directives, and the CSP can be output as a 
 * header or embedded in the HTML meta tags for better security.
 * 
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Config;

use \Luminova\Base\Configuration;

final class ContentSecurityPolicy extends Configuration
{
    /**
     * {@inheritdoc}
     * 
     * This method is called during the class instantiation to sets up the CSP directives.
     * 
     * @example - The example directives include:
     * 
     * - `default-src`: Allows resources to be loaded only from the same origin (`'self'`).
     * - `script-src`: Restricts scripts to be loaded from the same origin and 'https://scripts.example.com'.
     * - `img-src`: Allows images from any source.
     * - `style-src`: Allows inline styles and those from the same origin.
     * - `object-src`: Disables all embedded objects by setting it to `'none'`.
     * 
     * @see https://luminova.ng/docs/0.0.0/configs/csp
     */
    protected function onCreate(): void
    {
        /*
        $this->addCsp('default-src', ["'self'"])
            ->addCsp('script-src', ["'self'", 'https://scripts.example.com'])
            ->addCsp('img-src', ['*'])
            ->addCsp('style-src', ["'self'", "'unsafe-inline'"])
            ->addCsp('object-src', ["'none'"]);
        */
    }
}