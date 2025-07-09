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

use \Luminova\Base\Command;
use Luminova\Sessions\Session;
use \Luminova\Attributes\Group;
use \Luminova\Attributes\Route;
use \Luminova\Command\Utils\Color;

/**
 * Example CLI controller demonstrating how to implement
 * command-line tools using Luminova.
 */
#[Group(name: 'demo')]
class CommandController extends Command 
{
    /**
     * {@inheritdoc}
     * 
     * CLI group name.
     */
    protected string $group = 'demo';

    /**
     * {@inheritdoc}
     * 
     * Command name.
     */
    protected string $name  = 'app-command';

    /**
     * {@inheritdoc}
     * 
     * Usage instructions for the command.
     */
    protected array|string $usages  = [
        'php index.php demo <command> <arguments>',
        'php index.php demo app <arguments>'
    ];

    /**
     * {@inheritdoc}
     * 
     * Command description.
     */
    protected string $description = 'This CLI demo shows how to implement a routable CLI controller in Luminova.';

    /**
     * {@inheritdoc}
     * 
     * Command options.
     */
    protected array $options = [
        '--help' => 'Show command help information.',
        '--hello' => 'Display a "hello world" message.',
        '-l, --login' => 'CLI session login and persist session data.',
        '--logout' => 'Logout user session.',
        '-c, --color' => 'Apply color to hello world or custom message.',
        '-s, --select' => 'Prompt user to select an option.',
        '-p, --password' => 'Prompt user to enter a password.',
        '-m, --message' => 'Specify a custom message to display.'
    ];

    /**
     * {@inheritdoc}
     * 
     * Command examples.
     */
    protected array $examples = [
        'php index.php demo --help' => 'Show this help message.',
        'php index.php demo app --hello' => 'Show hello world message.',
        'php index.php demo app --password' => 'Prompt for a password.',
        'php index.php demo app --select' => 'Prompt to choose an option.',
        'php index.php demo app --message="Hi"' => 'Show a custom message.',
        'php index.php demo app --color="red"' => 'Show message with a specific color.',
    ];

    /**
     * Called when the command is created.
     */
    protected function onCreate(): void 
    {
        // Initialization code here
    }

    /**
     * Display help message.
     * 
     * @return int STATUS_ERROR
     */
    public function help(array $helps): int
    {
        return STATUS_ERROR;
    }

    /**
     * Demo "hello" command.
     * Handles color, select, password, and custom message options.
     *
     * @return int STATUS_SUCCESS or STATUS_ERROR
     * @see /routes/cli.php Command line route context.
     * 
     * @example - Command:
     * 
     * ```bash
     * php index.php demo hello <arguments>
     * ```
     */
    #[Route('app', group: 'demo')]
    public function hello(): int
    {
        $hello = $this->getOption('hello', null);
        $logout = $this->getOption('logout', null);
        $login = $this->getAnyOption('login', 'l');
        $color = $this->getAnyOption('color', 'c', null);
        $select = $this->getAnyOption('select', 's');
        $password = $this->getAnyOption('password', 'p');
        $message = $this->getAnyOption('message', 'm', 'Hello! This is the default message.');

        if ($login === true) {
            $this->write($this->loginUser());
            return STATUS_SUCCESS;
        }

        if ($logout === true) {
            $this->write($this->logoutUser());
            return STATUS_SUCCESS;
        }

        if ($color !== null && $color === '') {
            $this->error('You need to provide a color for argument --color.');
            return STATUS_SUCCESS;
        }

        if($hello === true){
            $this->write('Hello World!', $color);
            return STATUS_SUCCESS;
        }

        if ($select === true) {
            $message = $this->askForLanguages();
        } elseif ($password === true) {
            $message = $this->askForPassword();
        }

        if ($message === true) {
            $this->error('You need to provide a message for argument --message.');
            return STATUS_SUCCESS;
        }

        if($message){
            $this->write($message, $color);
            return STATUS_SUCCESS;
        }

        $this->error(sprintf(
            'Invalid command: "%s" not supported.',
            $this->getCaller()
        ));

        return STATUS_ERROR;
    }

    /**
     * Start PHP session in cli and persist id.
     * 
     * @return string Return message.
     */
    private function loginUser(): string
    {
        $sid = Session::toSessionId($this->getSystemId());

        $session = new Session();
        $session->setStorage('cli');

        if(!$session->start($sid)){
            return Color::style('Unable to start session in CLI', 'red');
        }

        if($session->isOnline()){
            return sprintf(
                "User is online.\nUsername: %s\nLast Login: %s\nSID: %s",
                $session->get('username'),
                $session->get('date'),
                $session->getId()
            );
        }

        $username = $this->input('Enter your username to login: ');

        if(!$username){
            return Color::style('Username is required', 'red');
        }

        $date = date('d-m-Y H:i:s A');
        $session->put('username', $username);
        $session->put('date', $date);
        
        if(!$session->login()){
            return Color::style('User login failed', 'red');
        }

        return sprintf(
            "Login successful.\nUsername: %s\nLogin Date: %s\nSID: %s",
            $username,
            $date,
            $sid
        );
    }

    private function logoutUser(): string
    {
        $sid = Session::toSessionId($this->getSystemId());

        $session = new Session();
        $session->setStorage('cli');

        if(!$session->start($sid)){
            return Color::style('Unable to start session in CLI', 'red');
        }

        if($session->isOnline()){
            $username = $session->get('username');
            $session->logout();

            return Color::style(sprintf(
                "User '%s' has successfully logged out.",
                $username
            ), 'green');
        }

        return Color::style('User has already logout', 'yellow');
    }

    /**
     * Prompt user to select a programming language.
     * 
     * @return string Selected language message.
     */
    private function askForLanguages(): string 
    {
        $options = [
            "green" => 'PHP',
            'JAVA',
            'JAVASCRIPT',
            'SWIFT',
            'PYTHON',
            'C++',
            "red" => 'HTML'
        ];

        $input = $this->prompt(
            'Enter your preferred programming language', 
            $options,
            'required|in_array(' . implode(',', $options) . ')'
        );

        return sprintf("Your preferred programming language is '%s'.", $input);
    }

    /**
     * Prompt user to enter a password and validate it.
     * 
     * @return string Password validation message.
     */
    private function askForPassword(): string 
    {
        $input = $this->password('Enter your password `12345`');

        if ($input === '12345') {
            return sprintf(
                "You have entered the correct password '%s'.",
                Color::style($input, 'green')
            );
        }

        return sprintf("Incorrect password '%s'.", $input);
    }
}
