### Application Controller Classes

To create a new application controller you must define the namespace for your controller as `namespace App\Controllers;`

#### Configuring Your Application

Your application class is located in `app/Controllers/` directory.
This is where you can configure how your application behave and most important do not forget to extend `BaseApplication`.
Your `Application` class should look something like this  

```php 
namespace App\Controllers;
use \Luminova\Base\BaseApplication;

class Application extends BaseApplication  {
    
}
```

In other to add custom configurations you need to create a contractor method `__construct` and initialize `parent::__construct()`, set the example below.

```php 
namespace App\Controllers;
use \Luminova\Base\BaseApplication;
use \Luminova\Sessions\Session;
use \Luminova\Sessions\SessionManager;

class Application extends BaseApplication  
{
    protected Session $session;

    public function __construct(string $dir = __DIR__){
        $this->session = Session::getInstance(new SessionManager('my_session_storage'));
        $this->session->start();

        parent::__construct($dir);
	}
}
```

####  Luminova\Base\BaseController

BaseController class is responsible in handling your view logics. Makes sure to extend it whenever you want create a new controller class.

An example of controller should be like 

```php 
namespace App\Controllers;
use Luminova\Base\BaseController;

class UserController extends BaseController
{
    /** @var \ Luminova\Http\Request $this->request */
    /** @var \ Luminova\Application $this->app */
    /** @var \Luminova\Security\InputValidator $this->validate */

}
```

####  Luminova\Base\BaseModel

Now to work with your new create controller you must initialize it parent class `parent::__construct()` inside the controller constructor method.

Now we can create a new Model `UserModel`, to use in `UserController` class, the model will handle your user information and user related logics.

To create a model you must do that inside `app/controllers/Models/`, your `UserModel` should have 3 protected properties defined `$table` The database table name where user information stored, `$primaryKey` The column key for user primary key and `$allowedFields` The allowed columns fields to update.

**Important Note:** While creating a model you must extend base `Model`

```php
namespace App\Controllers\Models;
use Luminova\Base\BaseModel;

class UserModel extends BaseModel
{
  protected string $table = 'users';
  protected string $primaryKey = 'user_id';
  protected array $allowedFields = [];
}
```

#####  Luminova\Base\BaseCommand

BaseCommand can be used to create a command controller class

```php
namespace App\Controllers;
use Luminova\Base\BaseCommand;

class CommandController extends BaseCommand {

    protected string $group = 'custom';
    protected string $name  = 'foo';
    protected string|array $usages  = 'Run php index.php <foo> <bar> <baz>';
    protected string $description = 'This is foo command description';
    protected array $options = [];

    public function run(?array $params = []): int
    {
        $options = $this->getOptions();
        $option = $this->getOption('view');
        $caller = $this->getCaller();
        $command = $this->getCommand();
        $argument = $this->getArgument(1);
        $value = $this->getValue("name");

        $this->writeln($options);
        $this->newLine();
        return parent::STATUS_OK;

    }
}
```

#### Controller Request Validation

Once done creating your user model you can handle other thing in `UserController`.
Import and prepare your `UserModel` in `UserController` by using `use` keyword.

Since you are going to be working on `UserController`, make sure to initialize its parent construction `parent::__construct()` in your `UserController` __construct method before anything.

```php
namespace App\Controllers;

use \Luminova\Base\BaseController;
use \App\Controllers\Models\UserModel;

class UserController extends BaseController
{
    protected function onCreate(){
        $this->validate->setRules([
            'name' => 'required',
            'age' => 'required|integer|min_length(2)|max_length(3)',
            'email' => 'required|email',
        ]);

        $this->validate->setMessages([
            'name' => [
                'required' => 'The name field is required.'
            ],
            'email' => [
                'required' => 'The email field is required.',
                'email' => 'The email address is not valid.'
            ],
            'age' => [
                'required' => 'The age field is required.',
                'integer' => 'The age must be an integer.',
                'min_length' => 'The age minimum length must 2.',
                'max_length' => 'The age maximum length must 3.'
            ]
        ]);
    }

    public function update(): void
    {
        $response = [
            'status' => 301,
            'message' => 'Invalid request'
        ];

        if ($this->request->getMethod() === 'post') {
            if ( $this->validate->validateEntries($this->request->getBody()) ) {
                $model = new UserModel();
                $data = [
                    'name' => $this->request->getPost("name"),
                    'email' => $this->request->getPost("email"),
                    'age' => $this->request->getPost("age"),
                    'last_update' => date('Y-m-d H:i:s')
                ];

                if ($model->updateRecord($this->request->getPost("id"), $data)) {
                    $response = [
                        'status' => 200,
                        'message' => 'Data updated successfully'
                    ];
                    /**
                    * Do your business here 
                    * $this->app->view("profile")->render(['userInfo' => $data]); Render view
                    * $this->app->redirect("/"); Redirect to main view or any view
                    */
                } else {
                    $response = [
                        'status' => 201,
                        'message' => 'Failed to update'
                    ];
                }
           }else{
               $response = [
                    'status' => 201,
                    'input' => $this->validate->getCurrentErrorField(),
                    'message' => $this->validate->getErrorLine()
                ];
                //print_r($this->validate->getErrors());
           }
        }

        $this->app->respond(json_encode($response), 'json');
    }

    public function profile($userId)
    {
        $model = new UserModel();
        $userInfo = $model->getRecord( $userId );
        if(empty($userInfo)){
            /**
             * Do your unknown business here
            */
            return;
        }
        $this->app->view("profile")->render(['userInfo' => $userInfo]);
    }
}
```

### BaseViewController

`BaseController` and `BaseViewController` are similar except that with `BaseViewController`, you have to initialize `validate(), request() and app()` before using it as its not initialized by default.

> This is suitable to use in your page controllers where you might not need `validate` and `request` instance.

```php
namespace App\Controllers;

use \Luminova\Base\BaseViewController;
use \App\Controllers\Application;
use \Luminova\Http\Request;
use \Luminova\Security\InputValidator;

class UserController extends BaseViewController
{

    protected ?Application $app = null;
    protected ?InputValidator $validate = null;
    protected ?Request $request = null;

    protected function onCreate(): void
    {
        $this->app ??= parent::app();
        $this->validate ??= parent::validate();
        $this->request = parent::request();
    }
}
```

## Application Routing & Controllers

Creating application template view file

[Template Views](VIEWS.md)