<?php

namespace App\classes;

class Upload {

    private $file,
            $uploaded = [],
            $failed,
            $specified_size = 10 * 1024 * 1024,
            $specified_size_in_mb = 1024 * 1024,
            $specified_size_in_kb = 1024,
            $specified_number = 5,
            $allowed = ['jpg','jpeg','png','gif'],
            $user_file_name,        // the name of the main file which is selected from the PC
            $file_name,             // file name only
            $file_name_new,
            $file_temp,
            $file_size,
            $file_error,
            $file_id,
            $file_size_type,
            $file_destination,
            $file_ext;              // file extension

    protected static $pdo;

    const CREDENTIALS = [
        'username'      => "root",
        'password'      => "",
        'host'          => "localhost",
        'dbname'        => 'uploads'
    ];

    public function __construct(){
        try {
            if(self::$pdo === NULL){
                self::$pdo = new \PDO('mysql:host=' . self::CREDENTIALS['host'] . ";dbname=" . self::CREDENTIALS['dbname'] , self::CREDENTIALS['username'] , self::CREDENTIALS['password']);
                echo "conneted with the database </br>";
            } 
            return self::$pdo;
        } catch(PDOException $e){
            die($e->getMessage());
        }
    }

    // the function to handle the upload of files
    public function upload_test($file){
        $this->file = $file;
        $this->file_name = $this->file['name'];
        if(!empty($this->file_name[0])) {
            if(count($this->file_name) <= $this->specified_number){
                for($i=0 ; $i < count($this->file_name) ; $i++){
                    if( !preg_match('/[^a-z0-9._]/i' , $this->file_name[$i]) ) {
                        if( strlen($this->validation_name($this->file_name[$i])) <= 64 && strlen($this->validation_name($this->file_name[$i])) >= 8) {
                            
                            // print_r($this->file_name[$i]);
                            $this->file_temp        = $this->file['tmp_name'][$i];
                            $this->file_size        = $this->file['size'][$i];
                            $this->file_error       = $this->file['error'][$i];
                            $this->file_ext         = explode('.',$this->file_name[$i]);
                            $this->file_ext         = strtolower(end($this->file_ext));
                            
                            if( in_array($this->file_ext , $this->allowed) ){
                                if( $this->file_error === 0 ){
                                    // the code of the size
                                    if( $this->file_size <= $this->specified_size ){
                                        $this->file_name_new    = uniqid('',true) . "." . $this->file_ext; 
                                        $this->file_name_new    = $this->validation_name($this->file_name_new);
                                        $this->user_file_name   = $this->file_name[$i] . "user"; 
                                        $this->file_destination = dirname(realpath(__DIR__)) . '/classes/files//' . $this->file_name_new;                                    

                                        if(move_uploaded_file($this->file_temp , $this->file_destination)){
                                            // echo "Yes";
                                            $stmt = self::$pdo->prepare("INSERT INTO files(hash,file_name,user_file_name,file_extension) VALUES(:hash_name,:file_name,:user_file_name,:file_extension)");
                                            $stmt->execute([
                                                ':hash_name'        =>  $this->file_name_new,
                                                ':file_name'        =>  $this->validation_name($this->file_name[$i]),
                                                ':user_file_name'   =>  $this->user_file_name[$i],
                                                ':file_extension'   =>  $this->file_ext
                                            ]);
                                            
                                            chmod($this->file_destination , 0644);

                                        } else {
                                            $this->failed[$i] = "the file  " . $this->file_name[$i] . " isn't uploaded";
                                        }
                                    } else {
                                        $this->failed[$i] = "The size of this file " . $this->file_name[$i] . " is large tahan 10mb";
                                    }

                                } else {
                                    $this->failed[$i] = $this->validation_name($this->file_name[$i]) . " the file has error " . $this->file_name[$i];
                                }          
                            } else {
                                $this->failed[$i] = $this->validation_name($this->file_name[$i]) . "failed to Upload this file " . $this->file_name[$i];
                            }
                        } else {
                            for($i=0; $i < count($this->file_name); $i++){
                                if( strlen($this->validation_name($this->file_name[$i])) < 8 || strlen($this->validation_name($this->file_name[$i])) > 64 ){
                                    echo "The name of this file '" . $this->file_name[$i] . "' is out of range and must be from 8 to 64 characters <br />";
                                }
                            }   
                        }
                    } else {
                        foreach( $this->file_name as $file ){
                            if( $this->validation_name($file) ){
                                echo $this->validation_name($file) . " must contain only letters and numbers <br>";
                            }
                        }
                    }
                }
            } else {
                echo "you have a " . $this->specified_number .  " as a maximum number of files to upload them";
            }
        } else {
            echo "Please Upload files";
        }
    }

    private function validation_name($file_name) {
        return htmlentities( str_replace(['*','/','\\','<','>','$','#','%','^','!','@','(',')','[',']','+','-','=','{','}','&','~' , '_'] ,'',$file_name ) ,ENT_QUOTES , "UTF-8");
    }

    private function filesize($file_size){
        $this->file_size = $file_size;

        if($this->file_size > 1024){
            $this->file_size = floor($this->file_size / 1024);
            $this->file_size_type = "kb"; 

            if($this->file_size > 1024){
                $this->file_size = floor($this->file_size / 1024);
                $this->file_size_type = "mb"; 
            } 
        } 
        return $this->file_size;        
    }

    public function useConnection(){
        return self::$pdo->query("SELECT * FROM files");
    }

    public function Download(){
        if(isset($_GET['file_id'])){
            $this->file_id = htmlentities((int) $_GET['file_id'],ENT_QUOTES,"UTF-8");
            $stmt2 = self::$pdo->prepare("SELECT * FROM files WHERE id = :file_id");
            $stmt2->execute([
                ':file_id'      =>  $_GET['file_id']
            ]);
            
            echo $stmt2->file['hash'];
            
            // echo $this->file->rowCount();
            if($stmt2->rowCount()){
                foreach($stmt2 as $bit){
                    $path = "App/files/{$bit['hash']}";
                    $path_down = "App/files/{$bit['file_name']}";
                    $file_download_name = $bit['file_name'];

                    header('Content-Type: application/octet-stream');
                    header('Content-Description: File Transfer');
                    header('Cache-Control: must-revalidate');
                    header("Content-Disposition: attachment; filename=\"{$file_download_name}\"");
                    header('Content-Length:' . filesize($path));
                    readfile($path);
                    exit;
                }
            }
        }
    }
}