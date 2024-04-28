<?php 

namespace App\Controllers;

use \Luminova\Base\BaseCommand;
use \Luminova\Command\TextUtils;
use \App\Controllers\Utils\Tables;
use \Luminova\Database\QueryBuilder;

/**
 * Class BlogCommand
 * 
 * Command-line tool for managing blog-related operations.
 */
class BlogCommand extends BaseCommand 
{
    /**
     * Command group.
     */
    protected string $group = 'blog';

    /**
     * Command name.
     */
    protected string $name  = 'blog-command';

    /**
     * Command usages.
     */
    protected array $usages  = [
        'php index.php blog --help',
        'php index.php blog list',
        'php index.php blog read id "foo"'
    ];

    /**
     * Command description.
     */
    protected string $description = 'CLI tool for managing blogs on our website.';

    /**
     * Command options.
     */
    protected array $options = [
        'php index.php blog list' => 'Get a list of blog',
        'php index.php blog read id "foo"' => 'Read a blog by its ID'
    ];

    /**
     * @var QueryBuilder $builder
    */
    private ?QueryBuilder $builder = null;

    /**
     * Initializes the query helper class
    */
    public function __construct()
    {
       parent::__construct();
       $this->builder ??= QueryBuilder::getInstance();
       $this->builder->caching(false);
    }

    /**
     * Display help information for the blog command.
     * php index.php blog --help
     * 
     * @param array $helps Help information for the command.
     * @return int Status code.
     */
    public function help(array $helps): int
    {
        return STATUS_ERROR;
    }

    /**
     * List all available blogs.
     * Command: php index.php blog list
     * 
     * @return int Status code.
     */
    public function list(): int
    {  
        // Retrieve blogs from the database
        $docs = $this->builder->table(Tables::DOCS)
        ->limit(10)
        ->select(['bid', 'blog_title']);
        
        // Display header
        $this->write($this->color('OUR BLOGS', 'red', null, TextUtils::ANSI_BOLD));
        $this->newLine();
        
        // Display each blog
        foreach($docs as $row){
            $this->writeln(TextUtils::padEnd($this->color('[' . $row->did . ']', 'green'), 40) . $row->blog_title);
        }

        return STATUS_SUCCESS;
    }

    /**
     * Display details of a specific blog.
     * Command: php index.php blog read id "2"
     * 
     * @param string $id Blog ID.
     * @return int Status code.
     */
    public function read(string $id): int
    {  
        // Retrieve blog from the database
        $tbl = $this->builder->table(Tables::DOCS);
        $tbl->where('bid', '=', $id);
        $blog = $tbl->find(['blog_description', 'blog_title']);
        //
        // Display blog details
        $this->write($this->color('[' . $blog->blog_title . ']', 'red', null, TextUtils::ANSI_BOLD));
        $this->newLine();
        $this->writeln($blog->blog_description);

        return STATUS_SUCCESS;
    }
}