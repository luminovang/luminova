<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers\Cli;

use \Luminova\Base\BaseCommand;
use \Luminova\Command\Utils\Color;
use \Luminova\Attributes\Route;
use \Luminova\Attributes\Group;

/**
 * This class demonstrates how CLI command line tools can be implemented in Luminova.
 */
#[Group(name: 'demo')]
class CliController extends BaseCommand 
{
    /**
     * {@inheritdoc}
     */
    protected string $group = 'demo';

    /**
     * {@inheritdoc}
     */
    protected string $name  = 'demo-command';

    /**
     * {@inheritdoc}
     */
    protected array|string $usages  = [
        'php index.php demo <command> <arguments>',
        'php index.php demo hello'
    ];

    /**
     * {@inheritdoc}
     */
    protected string $description = 'Luminova demo command line tool implementation example.';

    /**
     * {@inheritdoc}
     */
    protected array $options = [
        '--help' => 'Show command help information.',
        '-c, --color' => 'Show message with a specific color.',
        '-s --select' => 'Indicate to prompt user enter select an option.',
        '-p --password' => 'Indicate to prompt user to enter password.',
        '-m --message' => 'Specify message to display.'
    ];

    /**
     * {@inheritdoc}
     */
    protected array $examples = [
        'php index.php demo --help' => 'Show help message.',
        'php index.php demo hello' => 'Show default message.',
        'php index.php demo hello --password' => 'Show request for password.',
        'php index.php demo hello --select' => 'Show request to choose and option.',
        'php index.php demo hello --message="Hi"' => 'Show specified message.',
        'php index.php demo hello --color="red"' => 'Show message with a specific color.',
    ];

    /**
     * {@inheritdoc}
     */
    protected function onCreate(): void 
    {
        // Your initialization code here
    }

    /**
     * {@inheritdoc}
     */
    public function help(array $helps): int
    {
        return STATUS_ERROR;
    }

    /**
     * The command controller method hello.
     * This method will handle any demo command for hello.
     * 
     * @return int Return status code STATUS_SUCCESS or STATUS_ERROR.
     * 
     * @example Execute demo command:
     * ```bash 
     * php index.php demo hello arguments
     * ```
     * @see /routes/cli.php - The command line context.
     */
    #[Route('hello', group: 'demo')]
    public function hello(): int
    {
        $color = $this->getAnyOption('color', 'c', null);
        $select = $this->getAnyOption('select', 's');
        $password = $this->getAnyOption('password', 'p');
        $message = $this->getAnyOption('message', 'm', 'Hello! this is a test default message.');

        if($color !== null && $color === ''){
            $this->error('You need to provide a color for argument --color.');
            return STATUS_SUCCESS;
        }

        if($select === true){
            $message = self::askForLanguages();
        }elseif($password === true){
            $message = self::askForPassword();
        }

        if($message === true){
            $this->error('You need to provide a message for argument --message.');
            return STATUS_SUCCESS;
        }

        $this->write($message, $color);
        return STATUS_SUCCESS;
    }

    /**
     * Prompt user to select a programming language.
     * 
     * @return string Return message and selected language.
     */
    private function askForLanguages(): string 
    {
        $options = [
            "green" => 'PHP', // Use the array key to apply color.
            'JAVA',
            'JAVASCRIPT',
            'SWIFT',
            'PYTHON',
            'C++',
            "red" => 'HTML'
        ];

        $input = $this->prompt(
            'Enter your preferred programming language', // Message to display
            $options, // Array of options to prompt.
            'required|in_array(' . implode(',', $options) . ')' // Input validation rules.
        );

        return "Your preferred programming language is '{$input}'.";
    }

    /**
     * Prompt user to enter a password.
     * 
     * @return string Return password validation message.
     */
    private function askForPassword(): string 
    {
        // Password prompt.
        $input = $this->password('Enter your password `12345`');

        // Validate user password.
        if ($input == '12345') {
            $input = Color::style($input, 'green');
            
            return "You have entered the correct password '{$input}'.";
        }

        return "Incorrect password {$input}.";
    }
}
