<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OrderStagesBean extends Bean{
    protected $stages_id;
    protected $stages;
 
    public function getStages_id() {
        return $this->stages_id;
    }

    public function getStages() {
        return $this->stages;
    }

    public function setStages_id($stages_id) {
        $this->stages_id = $stages_id;
    }

    public function setStages($stages) {
        $this->stages = $stages;
    }


    
}
