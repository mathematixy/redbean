<?php
 /**
 * @name RedBean Cooker
 * @file RedBean
 * @author Gabor de Mooij and the RedBean Team
 * @copyright Gabor de Mooij (c)
 * @license BSD
 *
 * The Cooker is a little candy to make it easier to read-in an HTML form.
 * This class turns a form into a collection of beans plus an array
 * describing the desired associations.
 *
 * (c) G.J.G.T. (Gabor) de Mooij
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class Cooker {


	/**
	 * This method will inspect the array provided and load/dispense the
	 * desired beans. To dispense a new bean, the array must contain:
	 *
	 * array( "newuser"=> array("type"=>"user","name"=>"John") )
	 *
	 * - Creates a new bean of type user, property name is set to "John"
	 *
	 * To load a bean (for association):
	 *
	 * array( "theaddress"=> array("type"=>"address","id"=>2) )
	 * 
	 * - Loads a bean of type address with ID 2
	 *
	 * Now to associate this bean in your form:
	 *
	 * array("associations"=>array( "0" => "newuser-theaddress" ))
	 *
	 * - Associates the beans under keys newuser and theaddress.
	 *
	 * To modify an existing bean:
	 *
	 * array("existinguser"=>array("type"=>"user","id"=>2,"name"=>"Peter"))
	 *
	 * - Changes name of bean of type user with ID 2 to 'Peter'
	 *
	 * This function returns:
	 *
	 * array(
	 * 	"can" => an array with beans, either loaded or dispensed and populated
	 *  "pairs" => an array with pairs of beans to be associated
	 * );
	 *
	 * Note that this function actually does not store or associate anything at all,
	 * it just prepares two arrays.
	 *
	 * @static
	 * @param  $post the POST array containing the form data
	 * @return array hash table containing 'can' and 'pairs'
	 *
	 */
	public static function load($post) {

		//fetch associations first and remove them from the array.
		if (isset($post["associations"])) {
			$assoc = $post["associations"];
			unset($post["associations"]);
		}

		//We store beans here
		$can = $pairs = array();

		foreach($post as $key => $rawBean) {
			if (isset($rawBean["type"])) {
				//get type and remove it from array
				$type = $rawBean["type"];
				unset($rawBean["type"]);
				//does it have an ID?
				$idfield = "id";
				if (isset($rawBean[$idfield])) {
					//yupz, get the id and remove it from array
					$id = $rawBean[$idfield];
					//ID == 0, and no more fields then this is an NULL option for a relation, skip.
					if ($id==0 && count($rawBean)===1) continue;
					unset($rawBean[$idfield]);
					//now we have the id, load the bean and store it in the can
					$bean = R::load($type, $id);
				}
				else { //no id? then get a new bean...
					$bean = R::dispense($type);
				}
				//do we need to modify this bean?
				foreach($rawBean as $field=>$value){
					$bean->$field = $value;
				}
				$can[$key]=$bean;
			}
		}

		if (isset($assoc)) {
			foreach($assoc as $info) {
				$keys = explode("-", $info);
				$bean1 = $can[$keys[0]];
				$bean2 = $can[$keys[1]];
				$pairs[] = array( $bean1, $bean2 );
			}
		}

		return array("can"=>$can, "pairs"=>$pairs);

	}

}