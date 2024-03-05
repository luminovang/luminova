<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
 */
namespace App\Controllers\Models;

use \Luminova\Base\BaseModel;

class UserModel extends BaseModel
{
    /**
     *  Table name should be specified in child models.
     * @var string $table
    */
    protected $table = 'users'; 

    /**
     *  Default primary key column.
     * @var string $primaryKey
    */
    protected $primaryKey = 'uid';

     /**
     * Fields that can be inserted or updated.
     * @var array $allowedFields
    */
    protected $allowedFields = ['name', 'age', 'email']; 
    
}
