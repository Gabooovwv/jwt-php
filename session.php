<?php
ini_set("session.gc_probability", 1);
ini_set("session.gc_divisor", 1);
ini_set("session.gc_maxlifetime", 5);
session_start();
$lt = ini_get("session.gc_maxlifetime");
print_r($lt);
print_r($_SESSION);