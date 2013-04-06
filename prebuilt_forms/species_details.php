<?php

/**
 * Indicia, the OPAL Online Recording Toolkit.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/gpl.html.
 *
 * @package    Client
 * @subpackage PrebuiltForms
 * @author    Indicia Team
 * @license    http://www.gnu.org/licenses/gpl.html GPL 3.0
 * @link     http://code.google.com/p/indicia/
 */

/**
 * Displays the details of a single taxon. Takes an taxon_id in the URL and displays the following using a configurable
 * page template:
 * Record Details including custom attributes
 * An Explore Species' Records button that links to a custom URL
 * Any photos of occurrences with the same meaning as the taxon
 * A map displaying occurrences of taxa with the same meaning as the taxon
 * A grid showing the details of these occurrences.
 * @package    Client
 * @subpackage PrebuiltForms
 */


require_once('includes/dynamic.php');
require_once('includes/report.php');



class iform_species_details extends iform_dynamic {
    
  /** 
   * Return the form metadata. 
   * @return array The definition of the form.
   */
  public static function get_species_details_definition() {
    return array(
      'title'=>'View details of a species',
      'category' => 'Utilities',
      'description'=>'A summary view of a species including occurrence information. Pass a parameter in the URL called taxon to '.
        'define which species to show.'
    );
  }
  
  /** 
   * Return an array of parameters for the edit tab. 
   * @return array The parameters for the form.
   */
  public static function get_parameters() {   
    $retVal = array_merge(
    iform_map_get_map_parameters(),
      array(array(
        'name'=>'interface',
        'caption'=>'Interface Style Option',
        'description'=>'Choose the style of user interface, either dividing the form up onto separate tabs, '.
          'wizard pages or having all controls on a single page.',
        'type'=>'select',
        'options' => array(
          'tabs' => 'Tabs',
          'wizard' => 'Wizard',
          'one_page' => 'All One Page'
        ),
        'default' => 'one_page',
        'group' => 'User Interface'
      ),
      //List of fields to hide in the Record Details section
      array(
        'name' => 'fields',
        'caption' => 'Fields to include or exclude',
        'description' => 'List of data fields to hide, one per line. '.
            'Type in the field name as seen exactly in the Record Details section. For custom attributes you should use the system function values '.
            'to filter instead of the caption if defined below.',
        'type' => 'textarea',
        'required'=>false,
        'default' => '',
        'group' => 'Fields for record details'
      ),
      array(
        'name'=>'operator',
        'caption'=>'Include or exclude',
        'description'=>"Do you want to include only the list of fields you've defined, or exclude them?",
        'type'=>'select',
        'options' => array(
          'in' => 'Include',
          'not in' => 'Exclude'
        ),
        'default' => 'not in',
        'group' => 'Fields for record details'
      ),
      array(
        'name'=>'testagainst',
        'caption'=>'Test attributes against',
        'description'=>'For custom attributes, do you want to filter the list to show using the caption or the system function? If the latter, then '.
            'any custom attributes referred to in the fields list above should be referred to by their system function which might be one of: email, '.
            'cms_user_id, cms_username, first_name, last_name, full_name, biotope, sex_stage, sex_stage_count, certainty, det_first_name, det_last_name.',
        'type'=>'select',
        'options' => array(
          'caption'=>'Caption',
          'system_function'=>'System Function'
        ),
        'default' => 'caption',
        'group' => 'Fields for record details'
      ),
      //Allows the user to define how the page will be displayed.
      array(
      'name'=>'structure',
        'caption'=>'Form Structure',
        'description'=>'Define the structure of the form. Each component must be placed on a new line. <br/>'.
          "The following types of component can be specified. <br/>".
          "<strong>[control name]</strong> indicates a predefined control is to be added to the form with the following predefined controls available: <br/>".
              "&nbsp;&nbsp;<strong>[recorddetails]</strong> - displays information relating to the occurrence and its sample<br/>".
              "&nbsp;&nbsp;<strong>[explore]</strong> - a button “Explore this species’ records” which takes you to explore all records, filtered to the species.<br/>".
              "&nbsp;&nbsp;<strong>[photos]</strong> - photos associated with the occurrence<br/>".
              "&nbsp;&nbsp;<strong>[map]</strong> - a map that links to the spatial reference and location<br/>".
          "<strong>=tab/page name=</strong> is used to specify the name of a tab or wizard page (alpha-numeric characters only). ".
          "If the page interface type is set to one page, then each tab/page name is displayed as a seperate section on the page. ".
          "Note that in one page mode, the tab/page names are not displayed on the screen.<br/>".
          "<strong>|</strong> is used to split a tab/page/section into two columns, place a [control name] on the previous line and following line to split.<br/>",
          'type'=>'textarea',
          'default' => 
'=Record Details and Comments=
[recorddetails]
|
[photos]
=Online Map=
[map]
=Occurrence Details=
[occurrencedetails]
[explore]',
          'group' => 'User Interface'
       ),
       array(
         'name'=>'explore',
         'caption'=>'Explore URL',
         'description'=>'When you click on the Explore this species’ records button you are taken to this URL.
                        This URL should include http:// at the start as appropriate.',
         'type' => 'string',
         'required'=>false,
         'default' => '',
         'group' => 'User Interface'
       ),     
        array(
          'name' => 'include_layer_list',
          'caption' => 'Include Legend',
          'description' => 'Should a legend be shown on the page?',
          'type' => 'boolean',
          'required'=>false,
          'default'=>true,
          'group' => 'Other Map Settings'
        ),
        array(
          'name' => 'include_layer_list_switchers',
          'caption' => 'Include Layer switchers',
          'description' => 'Should the legend include checkboxes and/or radio buttons for controlling layer visibility?',
          'type' => 'boolean',
          'required'=>false,
          'default'=>true,
          'group' => 'Other Map Settings'
        ),
        array(
          'name' => 'include_layer_list_types',
          'caption' => 'Types of layer to include in legend',
          'description' => 'Select which types of layer to include in the legend.',
          'type' => 'select',
          'options' => array(
            'base,overlay' => 'All',
            'base' => 'Base layers only',
            'overlay' => 'Overlays only'
          ),
          'default' => 'base,overlay',
          'group' => 'Other Map Settings'
        ),
        array(
          'name' => 'layer_title',
          'caption' => 'Layer Caption',
          'description' => 'Caption to display for the species distribution map layer. Can contain replacement strings {species} or {survey}.',
          'type' => 'textfield',
          'group' => 'Distribution Layer'
        ),
        array(
          'name' => 'wms_feature_type',
          'caption' => 'Feature Type',
          'description' => 'Name of the feature type (layer) exposed in GeoServer to contain the occurrences. This must expose a taxon_meaning_id and a website_id attribute. '.
              'for the filtering. The detail_occurrences view is suitable for this purpose, though make sure you include the namespace, e.g. indicia:detail_occurrences. '.
              'The list of feature type names can be viewed by clicking on the Layer Preview link in the GeoServer installation.',
          'type' => 'textfield',
          'group' => 'Distribution Layer'
        ),
        array(
          'name' => 'wms_style',
          'caption' => 'Style',
          'description' => 'Name of the SLD style file that describes how the distribution points are shown. Leave blank if not sure.',
          'type' => 'textfield',
          'required' => false,
          'group' => 'Distribution Layer'
        ),
        array(
          'name' => 'cql_filter',
          'caption' => 'Distribution layer filter.',
          'description' => 'Any additional filter to apply to the loaded data, using the CQL format. For example "record_status<>\'R\'"',
          'type' => 'textarea',
          'group' => 'Distribution Layer',
          'required' => false
        ),
        array(
          'name' => 'refresh_timer',
          'caption' => 'Automatic reload seconds',
          'description' => 'Set this value to the number of seconds you want to elapse before the report will be automatically reloaded, useful for '.
              'displaying live data updates at BioBlitzes. Combine this with Page to reload to define a sequence of pages that load in turn.',
          'type' => 'int',
          'required' => false
        ),
        array(
          'name' => 'load_on_refresh',
          'caption' => 'Page to reload',
          'description' => 'Provide the full URL of a page to reload after the number of seconds indicated above.',
          'type' => 'string',
          'required' => false
        ),
       )
     );
     return $retVal;
   }
  
   
  /**
   * Override the getHidden function.
   * getForm in dynamic.php will now call this and return an empty array when creating a list of hidden input 
   * controls for form submission as this functionality is not being used for the Species Details page.
   * @package    Client
   * @subpackage PrebuiltForms
   */ 
  protected static function getHidden() {
    return NULL;
  } 
  
  
  /**
   * Override the getMode function.
   * getForm in dynamic.php will now call this and return an empty array when creating a mode list
   * as this functionality is not being used for the Species Details page.
   * @package    Client
   * @subpackage PrebuiltForms
   */ 
  protected static function getMode() {
    return array();
  }
   
  
 /**
  * Override the getAttributes function.
  * getForm in dynamic.php will now call this and return an empty array when creating an attributes list
  * as this functionality is not being used for the Species Details page.
  * @package    Client
  * @subpackage PrebuiltForms
  */ 
 protected static function getAttributes() {
   return array();
 }
 
  /**
   * Override the get_form_html function.
   * getForm in dynamic.php will now call this.
   * Vary the display of the page based on the interface type
   * 
   * @package    Client
   * @subpackage PrebuiltForms
   */ 
  protected static function get_form_html($args, $auth, $attributes) {
    if (isset($_POST['enable'])) {
      module_enable(array('iform_ajaxproxy'));
      drupal_set_message(lang::get('The Indicia AJAX Proxy module has been enabled.', 'info'));
    }
    if (!defined('IFORM_AJAXPROXY_PATH')) {
      $r = '<p>'.lang::get('The Indicia AJAX Proxy module must be enabled to use this form. This lets the form save verifications to the '.
        'Indicia Warehouse without having to reload the page.').'</p>';
      $r .= '<form method="post">';
      $r .= '<input type="hidden" name="enable" value="t"/>';
      $r .= '<input type="submit" value="'.lang::get('Enable Indicia AJAX Proxy').'"/>';
      $r .= '</form>';
      return $r;
    }  
      
    if (empty($_GET['taxon'])) {
      return 'This form requires a taxon parameter in the URL.';
    }

    global $user;
    data_entry_helper::$javascript .= 'indiciaData.username = "'.$user->name."\";\n";
    data_entry_helper::$javascript .= 'indiciaData.website_id = '.$args['website_id'].";\n";     
    data_entry_helper::$javascript .= 'indiciaData.ajaxFormPostUrl="'.iform_ajaxproxy_url($node, 'taxon')."&sharing=verification\";\n";

    return parent::get_form_html($args, $auth, $attributes);
  }

  
  /**
   * Draw the Species Details section of the page.
   * @return string The output html string.
   * 
   * @package    Client
   * @subpackage PrebuiltForms
   */
  protected static function get_control_recorddetails($auth, $args, $tabalias, $options) {
    iform_load_helpers(array('report_helper')); 
    $fields=helper_base::explode_lines($args['fields']);
    $fieldsLower=helper_base::explode_lines(strtolower($args['fields']));
    
    //If the user sets the option to exclude particular fields then we set to the hide flag
    //on the name types they have specified.
    if ($args['operator']=='not in') {
      $hidePreferred = false;
      $hideCommon = false;
      $hideSynonym = false;
      foreach ($fieldsLower as $theField) {
        if ($theField=='preferred names'|| $theField=='preferred name'|| $theField=='preferred')
          $hidePreferred = true;
        if ($theField=='common names' || $theField=='common name'|| $theField=='common')
          $hideCommon = true;
        if ($theField=='synonym names' || $theField=='synonym name'|| $theField=='synonym')
          $hideSynonym = true;
      }
    }
    
    //If the user sets the option to only include particular fields then we set to the hide flag
    //to true unless they have specified the name type.
    if ($args['operator']=='in') {
      $hidePreferred = true;
      $hideCommon = true;
      $hideSynonym = true;
      foreach ($fieldsLower as $theField) {
        if ($theField=='preferred names'|| $theField=='preferred name'|| $theField=='preferred')
          $hidePreferred = false;
        if ($theField=='common names' || $theField=='common name'|| $theField=='common')
          $hideCommon = false;
        if ($theField=='synonym names' || $theField=='synonym name'|| $theField=='synonym')
          $hideSynonym = false;
      }
    }
    //Draw the names on the page
    $details_report = self::draw_names($auth['read'], $hidePreferred, $hideCommon, $hideSynonym);

    $attrsTemplate='<div class="field ui-helper-clearfix"><span>{caption}:</span><span>{value}</span></div>';

    //draw any custom attributes for the species added by the user
    $attrs_report = report_helper::freeform_report(array(
      'readAuth' => $auth['read'],
      'class'=>'record-details-fields',
      'dataSource'=>'library/taxa/species_data_attributes_with_hiddens',
      'bands'=>array(array('content'=>$attrsTemplate)),
      'extraParams'=>array(
        'taxon_id'=>$_GET['taxon'],
        //the SQL needs to take a set of the hidden fields, so this needs to be converted from an array.
        'attrs'=>strtolower(self::convert_array_to_set($fields)),
        'testagainst'=>$args['testagainst'],
        'operator'=>$args['operator'],
        'sharing'=>'reporting'
      )
    ));

    $r .= '<h3>Species Details</h3>';
    //draw the species names and custom attributes
    if (isset($details_report))
      $r .= $details_report;
    if (isset($attrs_report))
      $r .= $attrs_report;
    return $r;
  }
  
  /**
   * Draw the names in the Species Details section of the page.
   * @return string The output html.
   * 
   * @package    Client
   * @subpackage PrebuiltForms
   */
  protected static function draw_names($auth, $hidePreferred, $hideCommon, $hideSynonym) {
    //Get all the different names for the species
    $record_details = report_helper::get_report_data(array(
      'readAuth' => $auth,
      'class'=>'record-details-fields',
      'dataSource'=>'library/taxa/species_data',
      'useCache' => false,
      'extraParams'=>array('taxa_taxon_list_id'=>$_GET['taxon'], 'sharing'=>'reporting')
    ));
    //Get all the common names for the species
    foreach ($record_details as $speciesNum=>&$speciesData) {
      if ($speciesData['name_type']=='common') {
        $commonNames .= $speciesData['?column?'].', ';
        //Remove the common names from the original data as we won't use it again 
        //and we want fewer records to cycle through later
        unset($record_details[$speciesNum]);
      }
    }
    //chop the space and comma off end of last item
    $commonNames = substr($commonNames, 0, -2);
    
    //Do exactly the same for synonyms as for common names
    foreach ($record_details as $speciesNum=>&$speciesData) {
      if ($speciesData['name_type']=='synonym') {
        $synonymNames .= $speciesData['?column?'].', ';
        unset($record_details[$speciesNum]);
      }
    }
    //chop the space and comma off end of last item
    $synonymNames = substr($synonymNames, 0, -2);
    //Hide items if there are no names in the section or the user has set the name type to be hidden
    if ($hidePreferred == false && !empty($record_details[0]['?column?']))
      $details_report .= "<body>Preferred name: ".$record_details[0]['?column?']."</body><br>";
    if ($hideCommon == false && !empty($commonNames))
      $details_report .= "Common names: $commonNames<br>";
    if ($hideSynonym == false && !empty($synonymNames))
      $details_report .= "Synonym names: $synonymNames</body>";
    
    return $details_report;
  }
  
  /**
   * Draw a grid listing all the occurrences of taxa with the same meaning as the supplied taxon.
   * @return string The output report grid.
   * 
   * @package    Client
   * @subpackage PrebuiltForms
   */
  protected static function get_control_occurrencedetails($auth, $args, $tabalias, $options) {
    $r = '<h3>Occurrence Details</h3>';
    $r .= data_entry_helper::report_grid(array(
    'id' => 'occurrences-grid',
    'dataSource' => 'library/taxa/species_occurrences',
    'mode' => 'report',
    'readAuth' => $auth['read'],
    'itemsPerPage' =>(isset($args['grid_num_rows']) ? $args['grid_num_rows'] : 10),
    'extraParams'=>array(
      'taxon_id'=>$_GET['taxon']
    )
    ));
    return $r;
  }

  /**
   * Draw Photos section of the page.
   * @return string The output report grid.
   * 
   * @package    Client
   * @subpackage PrebuiltForms
   */
  protected static function get_control_photos($auth, $args, $tabalias, $options) {
    iform_load_helpers(array('report_helper'));
    data_entry_helper::add_resource('fancybox');
    global $user;
    //default an items per page if not set by administrator
    if ($options['itemsPerPage'] == NULL) {
      $options['itemsPerPage'] = 12;
    }  
    //default a column count if not set by administrator
    if ($options['galleryColCount'] == NULL) {
      $options['galleryColCount'] = 3;
    }  
    
    //Use this report to return the photos
    $reportName = 'library/taxa/species_images';
    return '<h3>Photos</h3>'.report_helper::report_grid(array(
      'readAuth' => $auth['read'],
      'dataSource'=> $reportName,
      'itemsPerPage' => $options['itemsPerPage'],
      'columns' => array(
        array(
          'fieldname' => 'path',
          'template' => '<div class="gallery-item"><a class="fancybox" href="{imageFolder}{path}"><img src="{imageFolder}thumb-{path}" title="{caption}" alt="{caption}"/><br/>{caption}</a></div>'
        )
      ),
      'mode' => 'report',
      'autoParamsForm' => false,
      'includeAllColumns' => false,
      'headers' => false,
      'galleryColCount' => $options['galleryColCount'],
        'extraParams' => array(
        'taxon_id'=> $_GET['taxon'],
        'sharing'=>'reporting'
      )
    ));    
  }
  
  /**
   * Draw Map section of the page.
   * @return string The output map panel.
   * 
   * @package    Client
   * @subpackage PrebuiltForms
   */
  protected static function get_control_map($auth, $args, $tabalias, $options) {
    iform_load_helpers(array('map_helper', 'data_entry_helper'));
    global $user;
    // setup the map options
    $options = iform_map_get_map_options($args, $readAuth);
    $olOptions = iform_map_get_ol_options($args);
    $taxonId= $_GET['taxon'];
    $fetchOpts = array(
      'table' => 'taxa_taxon_list',
      'extraParams' => $auth['read'] + array(
          'view' => 'detail',
          'taxon_id' => $taxonId,
          'preferred' => true
      )
    );
    //Get data associated with the taxon
    $taxonData = data_entry_helper::get_population_data($fetchOpts);
    $meaningId = $taxonData[0]['taxon_meaning_id'];
    //get the records with the same meaning as the supplied taxon
    $fetchOpts = array(
      'table' => 'taxa_taxon_list',
      'extraParams' => $auth['read'] + array(
      'view' => 'detail',
      'language_iso' => iform_lang_iso_639_2($user->lang),
      'taxon_meaning_id' => $meaningId
      )
    );
    //Get taxa with the same meaning as the supplied taxon
    $taxaWithSameMeaningRecords = data_entry_helper::get_population_data($fetchOpts);
    $url = map_helper::$geoserver_url.'wms';
    // Get the style if there is one selected
    $style = $args["wms_style"] ? ", styles: '".$args["wms_style"]."'" : '';   
    map_helper::$onload_javascript .= "\n    var filter='website_id=".$args['website_id']."';";

    $layerTitle = str_replace('{species}', $taxaWithSameMeaningRecords[0]['taxon'], $args['layer_title']);
    map_helper::$onload_javascript .= "\n    filter += ' AND taxon_meaning_id=$meaningId';\n";

    if ($args['cql_filter']) 
      map_helper::$onload_javascript .= "\n    filter += ' AND(".str_replace("'","\'",$args['cql_filter']).")';\n";

    $layerTitle = str_replace("'","\'",$layerTitle);

    map_helper::$onload_javascript .= "\n    var distLayer = new OpenLayers.Layer.WMS(
      '".$layerTitle."',
      '$url',
      {layers: '".$args["wms_feature_type"]."', transparent: true, CQL_FILTER: filter $style},
      {isBaseLayer: false, sphericalMercator: true, singleTile: true}
    );\n";
    $options['layers'][]='distLayer';

    // This is not a map used for input
    $options['editLayer'] = false;
    // if in Drupal, and IForm proxy is installed, then use this path as OpenLayers proxy
    if (defined('DRUPAL_BOOTSTRAP_CONFIGURATION') && module_exists('iform_proxy')) {
      global $base_url;
      $options['proxy'] = $base_url . '?q=' . variable_get('iform_proxy_path', 'proxy') . '&url=';
     }
   
    // output a legend
    if (isset($args['include_layer_list_types']))
      $layerTypes = explode(',', $args['include_layer_list_types']);
    else
      $layerTypes = array('base', 'overlay');
    //Legend options set by the user
    if (!isset($args['include_layer_list']) || $args['include_layer_list'])
      $r .= map_helper::layer_list(array(
        'includeSwitchers' => isset($args['include_layer_list_switchers']) ? $args['include_layer_list_switchers'] : true,
        'includeHiddenLayers' => true,
        'layerTypes' => $layerTypes
      ));
    
    $r .= map_helper::map_panel($options, $olOptions);

    // Set up a page refresh for dynamic update of the map at set intervals
    if ($args['refresh_timer']!==0 && is_numeric($args['refresh_timer'])) { // is_int prevents injection
      if (isset($args['load_on_refresh']) && !empty($args['load_on_refresh']))
        map_helper::$javascript .= "setTimeout('window.location=\"".$args['load_on_refresh']."\";', ".$args['refresh_timer']."*1000 );\n";
      else
        map_helper::$javascript .= "setTimeout('window.location.reload( false );', ".$args['refresh_timer']."*1000 );\n";
    }
    
    $options = array_merge(
      iform_map_get_map_options($args, $auth['read']),
      $options
    );
   
    if ($args['interface']!=='one_page')
      $options['tabDiv'] = $tabalias;
    
    if (!isset($options['standardControls']))
      $options['standardControls']=array('layerSwitcher','panZoom');
    return '<h3>Map</h3>'.$r;  
  }
 
  /**
   * Draw the explore button on the page.
   * @return string The output HTML string.
   * 
   * @package    Client
   * @subpackage PrebuiltForms
   */
  protected static function get_control_explore($auth, $args) { 
    if (!empty($args['explore']))
      $r='<button type="button" onClick="parent.location='."'".$args['explore']."'".'">Explore this species’ records</button>' ;
    else 
      $r='<h5>The page has been setup to use an explore records button, but an "Explore URL" has not been specified.</h5>';
    return $r;
  }
 
  /**
   * When a form version is upgraded introducing new parameters, old forms will not get the defaults for the 
   * parameters unless the Edit and Save button is clicked. So, apply some defaults to keep those old forms
   * working.
   */
  protected function getArgDefaults($args) {
    if (!isset($args['interface']) || empty($args['interface']))
      $args['interface'] = 'one_page';
    
    if (!isset($args['hide_fields']) || empty($args['hide_fields']))
      $args['hide_fields'] = '';
    
    if (!isset($args['structure']) || empty($args['structure'])) {
      $args['structure'] = 
'=Record Details and Comments=
[recorddetails]
|
[photos]
=Online Map=
[map]
=Occurrence Details=
[occurrencedetails]
[explore]';
    }
    return $args;      
  }   
  
  /**
   * Disable save buttons for this form class. Not a data entry form...
   * @return boolean 
   */
  protected static function include_save_buttons() {
    return FALSE;  
  }
  
  /**
   * Used to convert an array of attributes to a string formatted like a set,
   * this is then used by the record_data_attributes_with_hiddens report to return
   * custom attributes which aren't in the hidden attributes list.
   * @return string The set of hidden custom attributes.
   * 
   * @package    Client
   * @subpackage PrebuiltForms
   */
  protected static function convert_array_to_set($theArray) {
    return "'".implode("','", str_replace("'", "''", $theArray))."'";
  }
}
?>