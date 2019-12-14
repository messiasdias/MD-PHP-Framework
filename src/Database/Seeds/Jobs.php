<?php
namespace App\Database\Seeds;
use App\Database\Seeder;
use App\Models\Job;

/**
 * Jobs Seeder Class
 */
class Jobs extends Seeder
{
	
	public function __construct($count){
		
		$i=1; 
		while ( $i <= $count ) {

			foreach ( glob('../assets/public/img/teste/*') as $img) {	
			 $img = str_replace('../assets/public', '', $img);

			  if ( $i <= $count ) {		

				$job = new Job();
				$job->id = $i;	
				$job->author_id = 1;
				$job->title = "Job {Teste} Item ".$i ;  
				$job->description = "Text Description of Job Teste Item ".$i;
				$job->img = $img ;
				$job->link = "https://www.lipsum.com/";
				$job->git = "https://www.lipsum.com/teste.git"; 
				$job->publish = 1;  

				$this->set_response( $job->create() , $job->title);
			
				$i++;

			  }
			  else{
				break;
			  }

			}


		} 


	}










}



?>