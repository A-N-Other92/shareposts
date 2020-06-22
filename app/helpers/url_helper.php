<?php
  // Simple page redirect
  function redirect($page){
   // echo $page; die();
    header('location: '.URLROOT.'/'.$page);
  }