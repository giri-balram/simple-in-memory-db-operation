<?php
namespace App\Models;

/**
 * Class InMemoryDatabase.
 */
class InMemoryDatabase
{
    /**
     * Storage

     * @var array
     */
    private $database = array();

    /**
     * Transactions

     * @var array
     */
    private $transactions = array();

    /**
     * Value usage counter

     * @var array
     */
    private $valueUsage = array();

    /**
     * Value transaction level counter

     * @var array
     */
    private $tranLevel = 0;

    /**
     * Open a transactional block
     *
     * @param null
     * @return mixed
     */
    public function beginTransaction()
    {
        //create a blank array in transaction block
        array_push($this->transactions, array());
        //increase the transaction level by 1
        $this->tranLevel = $this->tranLevel + 1;
    }

    /**
     * Rollback all of the commands from the most recent transaction block
     *
     * @throws Exception
     */
    public function rollback()
    {
        //check if there is any transaction block available
        if ($this->tranLevel > 0) {
            //get the last transaction block
            $lastTransaction = end($this->transactions);
            //unset the last transaction
            unset($this->transactions[key($this->transactions)]);
            //unset the value counter index for the coresponding block
            unset($this->valueUsage[$this->tranLevel]); // remove item at index
            $this->valueUsage = array_values($this->valueUsage); //reset the index
            //decrease the transaction block counter
            $this->tranLevel = $this->tranLevel - 1;
        } else {
            echo "NO TRANSACTION TO ROLLBACK\n";
        }
    }

    /**
     * Permanently store all of the operations from any presently open transactional blocks
     *
     * @param null
     * @return mixed
     */
    public function commit()
    {
        //check if there is any transaction block available
        if ($this->tranLevel > 0) {
            //rest the pointer to start point
            reset($this->transactions);

            //loop through the transaction block
            while ($this->tranLevel > 0) {

                //get the first transaction block
                $firstELement = current($this->transactions);

                //traverse all the value present in the first transaction block
                foreach($firstELement as $key => $value) {

                    //if database key is not there then increase the value usage. If it is there then check for duplicacy. if it is different then decrease the previous value usage and increase the new value usage
                    if( !isset($this->database[$key]) ){
                        $this->transactionValueChange($value,'up', 0);
                    } else if ( $this->database[$key] != $value ) {
                        $this->transactionValueChange($this->database[$key],'down', 0);
                        $this->transactionValueChange($value,'up', 0);
                    }

                    $this->database[$key] = $value;
                }
                //unset the transaction block value usege
                unset($this->valueUsage[$this->tranLevel]);
                //unset the transaction block
                unset($this->transactions[key($this->transactions)]);
                //reset the pointer if transaction block
                reset($this->transactions);
                //reset the pointer of value usage
                reset($this->valueUsage);
                //decrease the transaction level
                $this->tranLevel = $this->tranLevel - 1;
            }

        } else {
            echo "NO TRANSACTION TO COMMIT\n";
        }
    }

    /**
     * Set value

     * @param $name
     * @param bool $value
     */
    public function setValue($name = '', $value=false)
    {
        //check if there is any transaction block available
        if ($this->tranLevel > 0) {
            $lastTransaction = end($this->transactions);

            //if transaction block key is not there then increase the value usage. If it is there then check for duplicacy. if it is different then decrease the previous value usage and increase the new value usage
            if( !isset($lastTransaction[$name]) ){
                $this->transactionValueChange($value,'up', $this->tranLevel);
            } else if ( $lastTransaction[$name] != $value ) {
                $this->transactionValueChange($this->database[$name],'down', $this->tranLevel);
                $this->transactionValueChange($value,'up', $this->tranLevel);
            }

            //set the transaction block data
            $lastTransaction[$name] = $value;
            $key = key($this->transactions);
            //update the transaction block
            $this->transactions[$key] = $lastTransaction;

        } else {

            //if database key is not there then increase the value usage. If it is there then check for duplicacy. if it is different then decrease the previous value usage and increase the new value usage
            if( !isset($this->database[$name]) ){
                $this->transactionValueChange($value,'up', 0);
            } else if ( $this->database[$name] != $value ) {
                $this->transactionValueChange($this->database[$name],'down', 0);
                $this->transactionValueChange($value,'up', 0);
            }
            //update the database data
            $this->database[$name] = $value;
        }
    }

    /**
     * Return the value stored under the variable $name

     * @param $name
     * @return string
     */
    public function get($name)
    {
        //check if there is any transaction block available
        if ($this->tranLevel > 0) {
            //return the last transaction block value
            $lastTransaction = end($this->transactions);
            return $lastTransaction[$name] ??  'null';
        } else {
            //return the value from database
            return $this->database[$name] ?? 'null';
        }

    }

    /**
     * Unset variable

     * @param $name
     */
    public function delete($name)
    {
        //check if there is any transaction block available
        if ($this->tranLevel > 0) {
            //delete the data from current transaction block
            if(isset($this->transactions[$this->tranLevel-1][$name])){
                $value = $this->transactions[$this->tranLevel-1][$name];
                unset($this->transactions[$this->tranLevel-1][$name]);
                //update the value usage counter
                $this->transactionValueChange($value,'down', $this->tranLevel);
            }
        } else {
            //delete the data from database
            if(isset($this->database[$name])) {
                $value = $this->database[$name];
                $this->database[$name] = $value;
                unset($this->database[$name]);
                //update the value usage counter
                $this->transactionValueChange($value, 'down', 0);
            }
        }

    }

    /**
     * Update the number of variables equal to $value

     * @param $value - number to find
     * @param $status - up / down
     * @return mixed
     */
    private function transactionValueChange($value = '', $status = '', $whichLevel = '')
    {
        if ( !$value || !$status )
            return;

        //which level transaction // Database level = 0 Rest greater then 0
        $whichLevel = $whichLevel ?? $this->tranLevel;

        switch( $status ){
            case 'up'://increase
                $this->valueUsage[$whichLevel][$value] = isset($this->valueUsage[$whichLevel][$value]) ? $this->valueUsage[$whichLevel][$value] + 1 : 1;
                break;
            case 'down'://decrease
                $this->valueUsage[$whichLevel][$value] = isset($this->valueUsage[$whichLevel][$value]) ? $this->valueUsage[$whichLevel][$value] - 1 : 0;
                break;
            default:
                break;
        }

    }

    /**
     * Returns the number of variables equal to $value

     * @param $value
     * @return int
     */
    public function numEqualTo($value)
    {
        return isset($this->valueUsage[$this->tranLevel][$value]) ? $this->valueUsage[$this->tranLevel][$value] : 0;
    }

    /**
     * Rest all the data

     * @param null
     * @return null
     */
    public function reset()
    {
        $this->transactions = array();
        $this->database = array();
        $this->valueUsage = array();
        $this->tranLevel = 0;

    }
}