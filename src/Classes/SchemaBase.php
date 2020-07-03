<?php

namespace AMBERSIVE\Api\Classes;

class SchemaBase {

    public function __construct(array $params = [])
    {
        foreach($params as $key => $param){
            if (isset($this, $key)){
                $this->$key = $param;
            }
        }        
    }
    
    /**
     * Make an update for multiple keys
     *
     * @param  mixed $params
     * @return void
     */
    public function update(array $params = []){
        foreach($params as $key => $param){
            if (isset($this, $key)){
                $this->$key = $param;
            }
        }    
        return $this;
    }
    
    /**
     * Transform the current schema base to an array
     *
     * @return void
     */
    public function toArray(){
        return (array) $this;
    }

}