<?php
namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Repositories\DatabaseOperationRepository;
use App\Models\InMemoryDatabase;
use Illuminate\Support\Facades\DB;
use App\Exceptions\GeneralException;

class DatabaseOperationController extends BaseController
{
    /**
     * @var DatabaseOperationRepository
     */
    protected $dbOpRepository;
    protected $inMemoryDb;

    /**
     * DatabaseOperationController constructor.
     *
     * @param DatabaseOperationRepository $dbOpRepository
     */
    public function __construct(DatabaseOperationRepository $dbOpRepository)
    {
        $this->dbOpRepository = $dbOpRepository;
        $this->inMemoryDb = new InMemoryDatabase();
    }

    /**
     * @param Null
     *
     * @return mixed
     */
    public function readCommands()
    {
        //white a intial message
        fwrite(STDOUT, "\nHello! Please enter your command['GET', 'SET', 'UNSET' and 'NUMEQUALTO']. Program will terminate on command 'END'\n\n");


        $data = array();
        $transBlock = [];
        do {
            $command = trim(fgets(STDIN));
            $commandData = explode(' ', $command);
            if( env('IN_MEMORY' )){
                $this->executeInmemoryCommand($commandData);
            } else {
                $this->executeCommand($commandData);
            }

        } while ($command != 'END');
        fwrite(STDOUT, "\n-- Thank You For Executing Me --\n");
    }

    /**
     * @param string $command
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */

    function executeInmemoryCommand( $commandData ){
        try {

            switch ($commandData[0]) {
                case 'BEGIN':
                    $this->inMemoryDb->beginTransaction();
                    break;
                case 'ROLLBACK':
                    $this->inMemoryDb->rollBack();
                    break;
                case 'COMMIT':
                    $this->inMemoryDb->commit();
                    break;
                case 'GET':
                    if (count($commandData) != 2 ) {
                        echo "Invalid GET command\n";
                        break;
                    }
                    $data = $this->inMemoryDb->get($commandData[1]);
                    if ($data)
                        echo  $data."\n";
                    else
                        echo "NULL\n";
                    break;
                case 'SET':
                    if (count($commandData) != 3 ) {
                        echo "Invalid SET command\n";
                        break;
                    }
                    $this->inMemoryDb->setValue($commandData[1], $commandData[2]);
                    break;
                case 'UNSET':
                    if (count($commandData) != 2 ) {
                        echo "Invalid UNSET command\n";
                        break;
                    }
                    $this->inMemoryDb->delete($commandData[1]);

                    break;
                case 'NUMEQUALTO':
                    if (count($commandData) != 2 ) {
                        echo "Invalid NUMEQUALTO command\n";
                        break;
                    }
                    $data = $this->inMemoryDb->numEqualTo($commandData[1]);
                    if ($data)
                        echo $data."\n";
                    else
                        echo "0\n";

                    break;
                case 'END':
                    $this->inMemoryDb->reset();
                    break;
                default:
                    $this->inMemoryDb->reset();
                    fwrite(STDOUT, "There is no command found $commandData[0]!\n");
                    exit(0);
                    break;
            }

        } catch(GeneralException $e) {
            fwrite(STDOUT, $e->getMessage());
        }
    }

    /**
     * @param string $command
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    protected function executeCommand($commandData){
        try {

            switch ($commandData[0]) {
                case 'BEGIN':

                    DB::beginTransaction();
                    break;
                case 'ROLLBACK':
                    if( DB::transactionLevel() > 0 ) {
                        DB::rollBack();
                    } else {
                        fwrite(STDOUT, "NO TRANSACTION TO ROLLBACK\n");
                    }
                    break;
                case 'COMMIT':
                    if( DB::transactionLevel() > 0 ) {
                        $needtoclear = DB::transactionLevel();
                        for($i = 0; $i < $needtoclear; $i++ ) {
                            DB::commit();
                        }

                    } else {
                        fwrite(STDOUT, "NO TRANSACTION TO COMMIT\n");
                    }
                    break;
                case 'GET':
                    if (count($commandData) != 2 ) {
                        echo "Invalid GET command\n";
                        break;
                    }
                    $data = DB::table('inmemory_operations')->select('value')->where('name',$commandData[1])->first();

                    if ($data)
                        echo  $data->value."\n";
                    else
                        echo "NULL\n";

                    //echo $this->dbOpRepository->getData('name',$commandData[1])."\n";

                    break;
                case 'SET':
                    if (count($commandData) != 3 ) {
                        echo "Invalid SET command\n";
                        break;
                    }

                    DB::table('inmemory_operations')->updateOrInsert(['name'=>$commandData[1]],
                        ['name' => $commandData[1], 'value'=>$commandData[2]]
                    );
                    //$this->dbOpRepository->setData(array($commandData[1],$commandData[2]));

                    break;
                case 'UNSET':
                    if (count($commandData) != 2 ) {
                        echo "Invalid UNSET command\n";
                        break;
                    }

                    DB::table('inmemory_operations')->where('name', $commandData[1])->delete();
                    //$this->dbOpRepository->deleteData($commandData[1]);

                    break;
                case 'NUMEQUALTO':
                    if (count($commandData) != 2 ) {
                        echo "Invalid NUMEQUALTO command\n";
                        break;
                    }
                    $data = DB::table('inmemory_operations')->where('value', $commandData[1])->count();
                    if ($data)
                        echo $data."\n";
                    else
                        echo "0\n";

                    //echo $this->dbOpRepository->getRowCount($commandData[1])."\n";

                    break;
                case 'END':
                    DB::table('inmemory_operations')->truncate();
                    break;
                default:
                    DB::table('inmemory_operations')->truncate();
                    fwrite(STDOUT, "There is no command found $commandData[0]!\n");
                    exit(0);
                    break;
            }

        } catch(GeneralException $e) {
            fwrite(STDOUT, $e->getMessage());
        }
    }
}
