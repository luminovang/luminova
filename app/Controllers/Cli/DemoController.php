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

use Luminova\Attributes\Group;
use Luminova\Command\Terminal;
use Luminova\Sessions\Session;
use Luminova\Attributes\Command;
use Luminova\Command\Utils\Color;

/**
 * Example CLI controller demonstrating how to implement
 * command-line tools using Luminova.
 */
#[Group(name: 'demo')]
class DemoController extends \Luminova\Base\Command
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
        '--hello'           => 'Display a "hello world" message.',
        '--login'           => 'CLI session login and persist session data.',
        '--logout'          => 'Logout user session.',
        '-c, --color'       => 'Apply color to hello world or custom message.',
        '-s, --select'      => 'Prompt user to select an option.',
        '-p, --password'    => 'Prompt user to enter a password.',
        '-m, --message'     => 'Specify a custom message to display.',
        '-h, --help'        => 'Show command help information.',
    ];

    /**
     * {@inheritdoc}
     * 
     * Command examples.
     */
    protected array $examples = [
        'php index.php demo --help'             => 'Show this help message.',
        'php index.php demo app --hello'        => 'Show hello world message.',
        'php index.php demo app --password'     => 'Prompt for a password.',
        'php index.php demo app --select'       => 'Prompt to choose an option.',
        'php index.php demo app --message="Hi"' => 'Show a custom message.',
        'php index.php demo app --color="red"'  => 'Show message with a specific color.',
    ];

    /**
     * {@inheritdoc}
     * 
     * Called when the command is created.
     */
    protected function onCreate(): void 
    {
        // Initialization code here
    }

    /**
     * {@inheritdoc}
     */
    public function help(array $helps): int
    {
        return STATUS_ERROR;
    }

    /**
     * Demo command handler.
     * 
     * This method handles color, select, password, and custom message options.
     *
     * @return int STATUS_SUCCESS or STATUS_ERROR
     * @see /routes/cli.php Command line route context.
     * 
     * @example - Command:
     * 
     * ```bash
     * php index.php demo app <arguments>
     * ```
     */
    #[Command('app')]
    public function handler(): int
    {
        $login  = $this->input->getOption('login');
        $logout = $this->input->getOption('logout');
        $hello  = $this->input->getOption('hello', null);
        $color  = $this->input->getAnyOption('color', 'c', null);

        $result = match(true) {
            $login === true  => Terminal::write($this->loginUser()),
            $logout === true => Terminal::write($this->logoutUser()),
            $hello === true  => Terminal::write('Hello World!', $color),
            $color !== null && $color === '' 
                => $this->error('You need to provide a color for argument --color.'),
            default          => $this->showPromptMessage($color)
        };

        if($result === STATUS_ERROR){
            return STATUS_ERROR;
        }

        return STATUS_SUCCESS;
    }

    /**
     * Handle custom message or prompts.
     *
     * @param string|null $color The color display option.
     * 
     * @return int Return status code
     */
    private function showPromptMessage(?string $color): int
    {
        $select = $this->input->getAnyOption('select', 's');
        $password = $this->input->getAnyOption('password', 'p');
        $message = $this->input->getAnyOption('message', 'm', 'Hello! This is the default message.');

        if ($select === true) {
            $message = $this->askForLanguages();
        } elseif ($password === true) {
            $message = $this->askForPassword();
        }

        if ($message === true) {
            Terminal::error('You need to provide a message for argument --message.');
            return STATUS_SUCCESS;
        }

        if($message){
            Terminal::write($message, $color);
            return STATUS_SUCCESS;
        }

        Terminal::error(sprintf(
            'Invalid command: "%s" not supported.',
            $this->input->getInput()
        ));

        return STATUS_ERROR;
    }

    /**
     * Handle command login session logic.
     * 
     * This starts a PHP session in cli and persist id.
     * 
     * @return string Return message.
     */
    private function loginUser(): string
    {
        $id = Terminal::getSystemId();
        $sid = Session::toSessionId($id);

        $session = new Session();
        $session->setStorage('cli');

        if($session->isStrictMode()){
            $session->useStrictMode(false);
        }

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

        $username = Terminal::input('Enter your username to login: ');

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
            "Login successful.\nUsername: %s\nLogin Date: %s\nSID: %s\nID: %s",
            $username,
            $date,
            $sid,
            $id
        );
    }

    /**
     * Handle command logout logic.
     *
     * @return string Return logout status message.
     */
    private function logoutUser(): string
    {
        $id = Terminal::getSystemId();
        $sid = Session::toSessionId($id);

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

        $input = Terminal::prompt(
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
        $input = Terminal::password('Enter your password `12345`');

        if ($input === '12345') {
            return sprintf(
                "You have entered the correct password '%s'.",
                Color::style($input, 'green')
            );
        }

        return sprintf("Incorrect password '%s'.", $input);
    }
}