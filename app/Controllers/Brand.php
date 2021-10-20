<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Brand_Model;

class Brand extends ResourceController
{
    use ResponseTrait;

    //get all Brand
    public function showBrand()
    {
        $brand_model = new Brand_Model();
        $data['sp_brand'] = $brand_model ->where('S_statusid',3) -> findAll();
        return $this -> respond($data);
    }
    
    //show Product by Brand
    public function showProductbybrand(){
        $db = \Config\Database::connect();
        $builder = $db->table('sp_brand');
        $builder->join('sp_product','sp_product.B_brandid = sp_brand.B_brandid');
        $builder->where('sp_brand.B_brandid',$this->request->getVar('B_brandid'));
        $query = $builder->get();

        return json_encode($query->getResult());

    }
    //get Brand by id
    /*public function getBrand($id = null){
        $model = new Brand_Model();
        $data = $model->where('B_brandid',$id)->first();
        if($data){
            return $this->respond($data);
        }
        else{
            return $this->failNotFound('No Product found');
        }
    }*/

    

    public function showAmountSellProductByBrand(){
        $db = \Config\Database::connect();
        $sql = "SELECT sum(Od_amount) AS B_amound,B_name FROM `sp_ordertail`,`sp_order`,`sp_brand`,`sp_product` WHERE sp_product.B_brandid = sp_brand.B_brandid AND sp_product.P_productid = sp_ordertail.P_productid   AND sp_order.OS_statusid =6 AND sp_order.Or_orderid =sp_ordertail.Or_orderid GROUP BY B_name";
        $query = $db->query($sql);

        return json_encode($query->getResult());
    }

    //add new brand
    public function addBrand(){
        $db = \Config\Database::connect();
        $sql = "SELECT MAX(CAST(SUBSTRING(B_brandid, 3, 6) AS UNSIGNED)) AS maxid FROM sp_brand ";
        $query = $db->query($sql);
        $row = $query->getResult();
        $maxid = $row[0]->maxid;
        $code = '';
       if($maxid == null)
         {
            $code = 'BR0001';
         }else{
                $id = (string) $maxid + 1;
                $fullid =   str_pad($id,4,'0',STR_PAD_LEFT);
                $code = 'BR'.$fullid;

         }
        echo $code;

        $model = new Brand_Model();
        $data = [
            'B_brandid'=>$code,
            'B_name'=>$this->request->getVar('B_name'),
            'B_image'=>$this->request->getVar('B_image'),
            'S_statusid' => 3,
        ];
        $model->insert($data);
        $response =[
            'status' => 201,
            'error' => null,
            'message' => 'Brand creat successfully'
        ];
        return $this->respond($response);
   
    }


    //update brand
    public function updateBrand($id = null){
        $model = new Brand_Model();
        $data = [
            'B_name'=>$this->request->getVar('B_name'),
            'B_image'=>$this->request->getVar('B_image')
        ];
        $model->update($id,$data);
        $response =[
            'status' => 201,
            'error' => null,
            'message' => 'Brand update successfully'
        ];
        return $this->respond($response);
    }

    public function updateStatus($id=null){
        $brand_model = new Brand_Model();

        $data = [
            'S_statusid' => 4
        ];

        $brand_model->update($id, $data);
        $response = [
            'status' => 201,
            'error' => null,
            'message' => 'Delete success'
        ];
        echo $id;
        
        return $this->respond($response);

    }

    //delete brand
    public function deleteBrand($id=null){

        $db = \Config\Database::connect();
        $brand_model = new Brand_Model();
        $builder = $db->table('sp_product');
        $builder ->where('B_brandid',$id);
        
        if($builder ->countAllResults() == 0)

            {
            $brand_model->delete($id);
            $response =[
                'status' => 201,
                'error' => null,
                'message' => ['Brand delete successfully']
            ];
            return $this->respond($response);
        }
        else{
            return "Cannot Delete Brand Because Brand ".$id." Used in Product";
        }
    }
}