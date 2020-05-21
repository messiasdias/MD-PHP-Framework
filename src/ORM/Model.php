<?php
namespace App\ORM;
use Doctrine\ORM\Mapping as ORM;
use App\ORM\ModelInteface;
use App\ORM\DB;

/**
 *  @ORM\MappedSuperclass
 */
abstract class  Model implements ModelInterface {


    public function __construct($data=null){
      if(!is_null($data) && is_array($data)){
        foreach( $data as $key => $prop){
            if( property_exists(get_called_class(), $key) )
            {
              $method = 'set'.str_replace(' ','',ucwords(str_replace('_', ' ', $key))); 
              $this->$method($prop);
            }
        }
      }

      if(!is_null($data) && is_int($data) ){
        $model = self::find($data);
        if( is_a($model, get_called_class())  ){
            return $model;
        }
      }

    }

     /** 
      * @ORM\Id
      * @ORM\Column(type="integer")
      * @ORM\GeneratedValue
      */
    protected  $id;

      /** 
       * @ORM\Column(type="datetime")
       * @ORM\GeneratedValue
       *  */
    protected $created;

      /**
       * @ORM\Column(type="datetime")
       * @ORM\GeneratedValue
       * */
    protected $updated ;


    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return User
     */
    public function setCreated($created=null)
    { 
        $this->created = !is_null($created) ? $created : new \DateTime("now");
        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }


    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return User
     */
    public function setUpdated($updated=null)
    {
        $this->updated = !is_null($updated)? $updated :  new \DateTime("now") ;
        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }


    public static function getManager($class = '') {
      $db = new DB();
      return $db->getManager($class);
    }


    public function save(){

      $manager = self::getManager();
      $manager->persist($this);

      try {  
        $manager->flush();
      }catch(Exception $e){
        throw new Exception('Duplicate entry!');
      }
    }


    public function delete(){
      $manager = self::getManager();


      try {  
        $manager->remove($this);
        $manager->flush();
      }catch(Exception $e){
        throw new Exception('Detached entity cannot be removed !');
      }
    }


    public static function findBy(array $findBy= []){
      return self::getManager()->getRepository(get_called_class())->findBy($findBy);
    }

    public static function findOneBy(array $findOneBy = []){
      return self::getManager()->getRepository(get_called_class())->findOneBy($findOneBy);
    }

    public static function find(int $id){
      return self::getManager()->find(get_called_class(), $id);
    }

    public static function all(){
      return self::getManager()->getRepository(get_called_class())->findAll();
    }


}