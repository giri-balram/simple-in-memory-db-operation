<?php
namespace App\Repositories;

use App\Models\InmemoryOperation;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class DatabaseOperationRepository.
 */
class DatabaseOperationRepository extends BaseRepository
{

    /**
     * @return string
     */
    public function model()
    {
        return InmemoryOperation::class;
    }

    /**
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function setData(array $data)
    {
            $this->create([
                'name' => $data[0],
                'value' => $data[1],
            ]);
    }

    /**
     * @param string $columnName column Name
     * @param string $value column value
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function getData($columnName, $value)
    {
        try{
            $res = $this->where($columnName,$value)->first();
            if($res){
                return $res->value;
            } else {
                throw new ModelNotFoundException('Data not found');
            }
        } catch ( ModelNotFoundException $exception ){
            $msg = $exception->getMessage();
            return 'NULL';
        }

    }

    /**
     * @param integer $name
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function deleteData($name)
    {
        return $this->where('name',$name)->delete();
    }

    /**
     * @param integer $value
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function getRowCount($value)
    {
        return $this->where('value',$value)->count();
    }

}