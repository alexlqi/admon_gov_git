<?php
  include "class.diagram.php";

  $g = new Diagram();

  $arr = array('this' => array('is' =>    array('just' => array('a'),
                                                'test'),
                               'to' =>    array('test' => array('my',
                                                                'new' =>  array('class',
                                                                                'called'),
                                                                'diagram')),
                               'graph'));
  
  $g->SetRectangleBorderColor(124, 128, 239);
  $g->SetRectangleBackgroundColor(194, 194, 239);
  $g->SetFontColor(255, 255, 255);
  $g->SetBorderWidth(0);
  $g->SetData($arr);
  $g->Draw();
?>
