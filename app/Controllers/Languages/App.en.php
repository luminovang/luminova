<?php 
/**
 * @example locale('en') set local to english 
 * 
 * @example lang('App.error')
 * @example lang('App.invalid_user', null, null, [
 *  'name' => 'Peter',
 *   'email' => 'peter@example.com'
 * ])
*/

return [
    'error' => 'An error has occurred',
    'invalid_user' => 'Invalid user name {0} and email {1}'
];