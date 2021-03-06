<?php

class SoftwareComponentSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();
		
		$this->command->info('Create software components');
		
		// Initialize text sentence preprocessor
		$this->createIfNotExist(
				'textsentencepreprocessor',
				'This component is used for transforming text files',
				function( &$component ) { 
					$txtPreprocessor['domains'] = [];
					$txtPreprocessor['configurations'] = [];
				}
		);
	
		// Initialize media search component
		$this->createIfNotExist(
				'mediasearchcomponent',
				'This component is used for searching media in MongoDB',
				function( &$component ) {
					$component['keys'] = [];
					// add icons for formats in the database
					$component['formats'] = [
						'string' => 'fa-file-text-o', // do not show icons for string values
						'number' => 'fa-bar-chart',
						'time' => 'fa-calendar',
						'image' => 'fa-picture-o',
						'video' => 'fa-film',
						'sound' => 'fa-music'
					];
				}
		);
	}

	/**
	 * Creates a new software component, if it does not already exist in the SoftwareComponents 
	 * Collection.
	 * 
	 * @param $name Name of the software component to be created
	 * @param $label Label providing once sentence description of the component 
	 * @param $creator a function which configures any component specific settings. 
	 */
	private function createIfNotExist($name, $label, $creator) {
		$sc = SoftwareComponent::find($name);
		
		if( is_null($sc) ) {
			$this->command->info('...Initializing: ' . $name);
			$component = new SoftwareComponent($name, $label);	// Create generic component
			$creator($component);	// Customize anything specific for this component
			$component->save();		// And save the component
		}
	}
}
