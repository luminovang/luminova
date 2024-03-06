### Application Request Validations


#### Luminova\Security\InputValidator

With input InputValidator Class, you can validate user submitted information before saving it to database.
To do that you will have to set validation rules for for each input field and optional error messages for the set rules.

In your controller class, once you extend `BaseController` and initialize it, you can use input validation and request.
Or you can initialize the `InputValidator` class wherever you want to use it


#### Input Validation Rules

To set validation rules you can separate each rule with a parenthesis and parameter in bracket notation.

Adding rule one after another

```php
$this->validate->addRule('name', 'required|max_length(10)|alphanumeric', [
    'required' => 'Error message for required',
    'max_length' => 'Error message for max_length',
    'alphanumeric' => 'Error message for alphanumeric'
]);
```

Setting rules from array  
```php
$this->validate->setRules([
    'name' => 'required|max_length(10)|alphanumeric',
    'age' => 'required|integer|min_length(2)|max_length(3)',
    'email' => 'required|email',
], $optionalArrayOfErrors);
```
If you didn't specify the array of error messages on `setRules`, you can also set it in using the below method `setMessages`.

```php
$this->validate->setMessages([
    'name' => [
         'required' => 'Error message for required',
        'max_length' => 'Error message for max_length',
        'alphanumeric' => 'Error message for alphanumeric'
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
```

##### Available Validations

Rule           | Parameter    | Description
---------------|--------------|------------------------------------------------
none           |  Void        | Ignore filed and return true
required       |  Void        | Field is required, it cannot be empty
max_length()   |  Integer     | Field maximum allowed length
min_length()   |  Integer     | Field minimum allowed length
alphanumeric   |  Void        | Field should must be only alphanumeric [aZ-Az-0-9]
email          |  Void        | Field should must be a valid email address
integer()      |  String      | Field should must an integer value, optional parameter [positive or negative] [-0-9]
equals()       |  String      | Field value must match with anther specified field value 
url            |  Void        | Field must be a valid URL
alphabet       |  Void        | Field must be only an alphabet [aZ-Az]
uuid()         |  Integer     | Field value must be uuid string, optional parameter for uuid version 
exact_length() |  Integer     | Field value must be exact length
in_array()     |  String      | Field value must be in array list
keys_exist()   |  String      | Field array values must match in validation list
callback()     |  Callable    | Callback myFunction(value, field) return boolean value
ip()           |  Integer     | Field value must be a valid IP address, optional parameter for ip version
decimal        |  Void        | Field must be a valid decimal value
match()        |  Regex       | Field value must match the regular expression [/pattern/] 
fallback()     |  Mixed       | If the field value is empty, replace it withe the default value, if parameter is empty any empty string will be used instead
phone          |  Void        | Check if field value is a phone number
binary         |  Void        | Check if field value is a binary
hexadecimal    |  Void        | Check if field value is a hexadecimal
array          |  Void        | Check if field value is a array
json           |  Void        | Check if field value is a json object
path()         |  String      | Check if field value is directory path, if parameter [true] is specified it will check if path is readable 
scheme()       |  String       | Check if field value url scheme matched with the specified url scheme


##### Validation Class Methods

Method Name            |    Description 
-----------------------|------------------------------------------------
validateEntries(array $input, array $rules = []): bool  | To validate input entries, it return true or false. The second parameter is optional.
validateField(string $ruleName, string $value, string $rule, mixed $param = null): bool  | To validate fields processed by `validateEntries` method it return true or false. The third parameter is optional
getErrors(): array | Get validation error messages 
getError(string $field): string | Get validation error message by field name
getErrorLine(nt $indexField = 0, int $indexErrors = 0): string | Get validation error message by field index and error index
setRules(array $rules, array $messages = []): self | Set validation rules with optional messages
addRule(string $field, string $rules, array $messages = []): self | Add validation rule with optional messages
addError(string $field, string $ruleName, string $message): self | Add a single validation error to field
setMessages(array $messages): self | Set validation messages
addMessage(string $field, array $messages): self | Add validation message to field
listToArray(string $list): array | Convert string list to array `a,b,c` to `[a, b, c]`
listInArray(string $list, array $map): bool | Check if list elements exists in an array 

##### Implementing Validation 

To implement your custom validation extend `Luminova\Security\ValidatorInterface`

## Application HTTP Requests

Making http request and receiving and responding to requests

[Request & Validations](REQUEST.md)