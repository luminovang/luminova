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

use \Luminova\Base\BaseViewController;

class Home extends BaseViewController 
{
    /** @method \Luminova\Http\Request $this->request() */
    /** @method \Luminova\Application $this->app */
    /** @method \Luminova\Security\InputValidator $this->validate() */
    /** @method \Luminova\Template\Template $this->view(string $view, array $options = []): int 0 */
    /** @method \Luminova\Library\Importer $this->library() */
    
    public function page(): void
    {
        $this->view('index', [
            'foo' => 'bar'
        ]);

        /* Same as above
        $this->app->view("index")->render([
            'foo' => 'bar'
        ]);
        */
    }
}
