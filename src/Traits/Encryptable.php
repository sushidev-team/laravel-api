<?php

namespace AMBERSIVE\Api\Traits;

use DB;
use Crypt;
use Illuminate\Http\Request;
use App\Traits\Uuids;

trait Encryptable
{
    /**
     * Get a specific attribtue value
     */
    public function getAttribute($key)
    {

        $value = parent::getAttribute($key);

        if ($key === "" || $key === null) {
            return $value;
        }

        if (in_array($key, $this->encryptable) && $value !== '' && $value !== null) {
            $value = decrypt($value);
        }
        return $value;
        
    }

    /**
     * Update a specific attribute value
     */
    public function setAttribute($key, $value)
    {

        if (empty($this->encryptable) == false && in_array($key, $this->encryptable)) {
            $value = Crypt::encrypt($value);
        }

        if ($key === "" || $key === null) {
            return;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * When need to make sure that we iterate through
     * all the keys.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();
        foreach ($this->encryptable as $key) {
            if (isset($attributes[$key])) {
                $attributes[$key] = decrypt($attributes[$key]);
            }
        }
        return $attributes;
    }

}
