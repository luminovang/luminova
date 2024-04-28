<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers;

use \Luminova\Base\BaseController;

class UserController extends BaseController
{
    /** @var \Luminova\Http\Request $this->request */
    /** @var \Luminova\Application $this->app */
    /** @var \Luminova\Security\InputValidator $this->validate */
    /** @var \Luminova\Template\Template $this->view(string $view, array $options = []): int 0 */
    /** @var \Luminova\Library\Importer $this->library() */


    public function page(): void
    {
        $this->view('index');
    }
}
