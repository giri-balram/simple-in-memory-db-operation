# simple-in-memory-db-operation
This application will create a very simple in-memory database which has a very limited command set of most common operations are GET, SET, UNSET and NUMEQUALTO. All of the commands are going to be fed to one line at a time via stdin, and the job is to process the commands and to perform whatever operation the command dictates.

In addition to the data commands, your database should support transactions, accepting the following commands:

BEGIN:Open a transactional block.
ROLLBACK:Rollback all of the commands from the ​most recent​transactional block.
COMMIT:Permanently store all of the operations from all presently open transactional blocks.

Both ROLLBACK and COMMIT cause the program to print 'NO TRANSACTION' if there are no open transaction blocks.

Your database needs to support nested transactions. ROLLBACK only applies to the most recent tranaction block, but COMMIT applies to all transaction blocks. (Any data command run outside of a transaction is committed immediately.)

## Examples

So here is a sample input:


    SET a 10

    GET a

    UNSET a

    GET a

    END
And its corresponding output:


    10

    NULL
    
And another one:

    SET a 10

    SET b 10

    NUMEQUALTO 10

    NUMEQUALTO 20

    UNSET a

    NUMEQUALTO 10

    SET b 30

    NUMEQUALTO 10

    END
And its corresponding output:

    2

    0

    1

    0

## Prerequisite 

As it is build on the Laravel framework, it has a few system requirements. Of course, all of these requirements are satisfied by the Laravel Homestead virtual machine, so it's highly recommended that you use Homestead as your local Laravel development environment.

However, if you are not using Homestead, you will need to make sure your server meets the following requirements:

- PHP >= 7.1.3
- MySql >= 5.7
- Composer
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- Ctype PHP Extension
- JSON PHP Extension
- BCMath PHP Extension

You can check all the laravel related dependecies <a href="https://laravel.com/docs/5.7/installation#server-requirements"  target="_blank"> here </a>.

## Installation steps

Follow the bellow steps to install and set up the application.

**Step 1: Clone the Application**<br>
You can download the ZIP file or git clone from my repo into your project  directory.

**Step 2: Configure the Application**<br>
After you clone the repo in to your project folder the project need to be set up by following commands-

- In terminal go to your project directory and Run 
    
        composer install 
    
- Then copy the .env.example file to .env file in the project root folder
    
- To set the Application key run the bellow command in your terminal.
    
        php artisan key:generate
    
- Make your storage and bootstrapp folder writable by your application user.

- For inmemory db opeartion we dont need any database connection so we don't need any migration file. To enable inmemory opeartion please set **'IN_MEMORY' env value 'true'** . 

- If you want to use database connection and migration please follow the bellow two point and set **'IN_MEMORY' env value 'false'**. I have used here **'sqlite'** database. 

- Create all the necessary tables need for the application by runing the bellow command.
    
        php artisan migrate

- Fill default data if your need by running bellow command.

        php artisan db:seed

Thats all! The application is configured now.


## Execute command

- Open the project root folder in terminal.

- I have created a console commad to feed stdin input to the application. Type  bellow command

         php artisan inmemory:dboperation

- Then type your required command to see the output
