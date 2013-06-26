<?php
    class sm_jsbehaviors {
        private $s = '';
        private $root_jq_selector;
        public function render($data, $root_jq_selector){
            $this->root_jq_selector = $root_jq_selector;
            $s = "";
            foreach($data as $dataitem){
                if (isset($dataitem['behavior'])){
                    foreach ($dataitem['behavior'] as $behavior){
                        $clsname = $behavior['type']. "_bhvr";
                        $s .= $this->$clsname($dataitem, $behavior);
                    }
                }
            }
            
            return $s;
        }
        
        protected function hidden_unless_bhvr($calledby, $behavior){
            $observed = $behavior['observed'];
            $target = $calledby['name'];
            $conditions = $behavior['conditions']['value_in'];
            $js_in_array = "[\"" . implode("\",\"", $conditions) . "\"]";
            
            $s = "$(\"#{$target}_wrap\", $(\"{$this->root_jq_selector}\")).hide();";
            $s .= "$(\"#{$observed}_form\", $(\"{$this->root_jq_selector}\")).change(function(){\n";
            $s .= " var v = $(this).val();\n";
            $s .= " if (jQuery.inArray(v, $js_in_array) == -1){\n";
            $s .= " $(\"#{$target}_wrap\", $(\"{$this->root_jq_selector}\")).hide();";
            $s .= "} else {";
            $s .= " $(\"#{$target}_wrap\", $(\"{$this->root_jq_selector}\")).show('slow');";
            $s .= "}\n";
            $s .= "});\n";
           
            return $s;
        }
        
    }
     
    /*   
"behavior": [
{
"observed": "work_type_monovalue",
"conditions": {
"value_in": [
"work_type_monovalue__repair",
"work_type_monovalue__maintenance"
]
},
"type": "hidden_unless"
}
],
*/