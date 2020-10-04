<?php

  ///////////////////////////////////////////
 ////  GUI Builder  ////////////////////////
///////////////////////////////////////////

class Gui_Builder{

    function __construct(){

    }

    public function page_content_title($label){
        $output = "
            <div>
                <h2 style=\"color:white;font-size:20px;border-bottom: 1px solid #e0e0e3;padding-bottom:5px;margin:10px 10px;\">".$label."</h2>
            </div>";
        return $output;
    }

    public function section_heading($icon, $label, $tooltip=NULL){
        $heading = "
        <div style=\"border-radius:0px; border-bottom: 1px solid #e0e0e3; background:none; margin:0px; padding:0px 0px 5px 0px;\">
            <h2 style=\"display:inline;\"><i class=\"".$icon."\" aria-hidden=\"true\"></i><label class=\"nav-icon-label\">".$label."</label></h2>";
            if($tooltip != NULL){
                $heading .=
                    "<h2 style=\"float:right; display:inline;\">
                    <div class=\"tooltip\"><i class=\"far fa-question-circle\"></i>
                        <span class=\"tooltiptext\">".$tooltip."</span>
                    </div>
                    </h2>";
            }
            $heading .= "</div>";
        return $heading;
    }

    public function section_message($error=NULL, $message=NULL){
        $output = "";
            if($error != NULL){
                $output .= $this->compose_message("error", $error);
            }
            if($message != NULL){
                $output .= $this->compose_message("message", $message);
            }
		return $output;
    }

    public function compose_message($type, $content){
        $format = "message";
        if($type == "error"){
            $format = "error";
        }
        $output = "<div id=\"section_message\" class=\"".$format."\">".$content."</div>
                    <script>
                        setTimeout(function(){
                            document.getElementById('section_message').style.display = 'none';
                        }, 5000); // 5000ms = 5s
                    </script>";
        return $output;
    }

    function __destruct(){
    }
}
?>