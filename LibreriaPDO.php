<?php

class DB
{
    //Establecemos una propiedad privada para cada uno de los parámetros de conexión
    
    private $pdo;  //Propiedad que recoge el objeto PDO creado tras la conexión
    
    private $host="localhost";
    private $user="root";
    private $pass="";
    private $base ="equipos";  //Nombre de la BBDD a la que nos vamos a conectar
    
    public $filas=array();  //Array de filas con los resultados de las consultas de datos
    
    function __construct($base)  //El constructor recibe el nombre de la BBDD a la que nos vamos a conectar   
    {
      $this->base=$base;  
    }
    
    private function Conectar()
    {
        try
        {
            $this->pdo=new PDO("mysql:host=$this->host;dbname=$this->base",$this->user,$this->pass);
            
            //Para que genere excepciones a la hora de reportar errores.
            
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }
        
     
    }
    
    //Método para ejecutar consultas de tipo INSERT, UPDATE o DELETE. Recibe la consulta y un array con los parámetros de sustitución
    public function ConsultaSimple($consulta,$param=array())
    {
      try  
      {
         $this->Conectar();  //Nos conectamos al servidor de BBDD
          
         $sta=$this->pdo->prepare($consulta); //Preparamos la consulta
         
         $sta->execute($param);     //Y la ejecutamos con el array de parámetros de sustitución
         
         $this->Cerrar();    //Cerramos la conexión
         return true;
      }
      catch(PDOException $e)
      {
          if (isset($this->pdo)) {
              $this->Cerrar();
          }
          echo $e->getMessage();
          return false;
      }
        
    }
 
    //Método para ejecutar consultas de tipo SELECT. Recibe la consulta y un array con los parámetros de sustitución
    public function ConsultaDatos($consulta,$param=array())
    {
        try
        {
            $this->Conectar();  //Nos conectamos al servidor de BBDD
            
            $sta=$this->pdo->prepare($consulta); //Preparamos la consulta
            
            $sta->execute($param);     //Y la ejecutamos con el array de parámetros de sustitución
            
            $this->filas=$sta->fetchAll(PDO::FETCH_ASSOC);  //Devolvemos las filas de la consulta en la propiedad pública filas
            
            $this->Cerrar();    //Cerramos la conexión
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }
        
    }
    
   //Método para cerrar la conexión con la BBDD
    private function Cerrar()
    {
        $this->pdo=null;
           
    }
     
}










?>