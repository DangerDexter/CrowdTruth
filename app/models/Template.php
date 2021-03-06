<?php

use \Counter as Counter;

class Template extends Moloquent {
	protected $collection = 'templates';
	protected $softDelete = true;
	protected static $unguarded = true;
	public static $snakeAttributes = false;
	
	// TODO: add parameters to Constructor
	public function __construct() {
		$this->filterResults ();
		parent::__construct ();
	}
	
	public function filterResults() {
		$input = Input::all ();
		if (array_key_exists ( 'wasAssociatedWithUserAgent', $input ))
			array_push ( $this->with, 'wasAssociatedWithUserAgent' );
		if (array_key_exists ( 'wasAssociatedWithCrowdAgent', $input ))
			array_push ( $this->with, 'wasAssociatedWithCrowdAgent' );
		if (array_key_exists ( 'wasAssociatedWithSoftwareAgent', $input ))
			array_push ( $this->with, 'wasAssociatedWithSoftwareAgent' );
		if (array_key_exists ( 'wasAssociatedWith', $input ))
			$this->with = array_merge ( $this->with, array (
					'wasAssociatedWithUserAgent',
					'wasAssociatedWithCrowdAgent',
					'wasAssociatedWithSoftwareAgent' 
			) );
	}
	
	public static function boot() {
		parent::boot ();
		
		static::saving ( function ($template) {
			if (! Schema::hasCollection ( 'templates' )) {
				static::createSchema ();
			}
			
			static::validateTemplate($template);
		} );
	}
	
	private static function validateTemplate($template) {
		if (is_null ( $template->_id )) {
			$template->_id = static::generateIncrementedBaseURI ( $template );
		}
		
		if (is_null ( $template->user_id )) {
			$template->user_id = Auth::user ()->_id;
		}
		
		// All json documents must have: _id, updated_at, created_at. These are
		// automatically generated by MongoDB
		// Template must also have: platform, type, instructions, cml/html, css, js, version, project, user_id		
		$checkFor = [ "platform", "type", "instructions", "cml", "css", "js", "version", "user_id" ];
		foreach ($checkFor as $field) {
			if(is_null($template->$field)) {
				throw new \Exception('Template does not comply with data model! -- '.$field.' missing');
			}
		}
	}
	
	public static function generateIncrementedBaseURI($template) {
		$seqName = 'template' . '/' . $template->platform;
		$id = Counter::getNextId ( $seqName );
		return $seqName . '/' . $id;
	}

	public static function createSchema() {
		Schema::create ( 'templates', function ($collection) {
			$collection->index ( 'hash' );
			$collection->index ( 'version' );
			$collection->index ( 'type' );
			$collection->index ( 'activity_id' );
			$collection->index ( 'user_id' );
		} );
	}

    public function wasAssociatedWithUserAgent(){
        return $this->hasOne('UserAgent', '_id', 'user_id');
    }

    public function wasAssociatedWithCrowdAgent(){
        return $this->hasOne('CrowdAgent', '_id', 'crowdAgent_id');
    }    

    public function wasAssociatedWithSoftwareAgent(){
        return $this->hasOne('SoftwareAgent', '_id', 'softwareAgent_id');
    }
}
