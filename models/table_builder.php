<?php

/**********
bootsole, v0.1.1

Copyright 2014 by Alex Weissman

MIT License:
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the 'Software'), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
**********/

require_once("template_functions.php");

class TableBuilder {

    protected $_columns = array();
    
    protected $_rows = array();
    
    protected $_menu_items = array();
    
    protected $_menu_label;
    protected $_menu_state_field;
    protected $_menu_style_field;   
    
    public function __construct($columns, $rows = array(), $menu_items = array(), $menu_label = "Status/Actions", $menu_state_field = null, $menu_style_field = null) {
        $this->_columns = $columns;
        $this->_rows = $rows;
        $this->_menu_items = $menu_items;
        $this->_menu_label = $menu_label;
        $this->_menu_state_field = $menu_state_field;
        $this->_menu_style_field = $menu_style_field;           
    }
    
    public function addRow($row){
        $this->_rows[] = $row;
    }        
        
    public function render(){
        // Generate initial sort
        $initial_sort = "[";
        $i = 0;
        foreach($this->_columns as $column_name => $column) {
            if (isset($column['sort'])){
                if ($column['sort'] == "asc")
                    $initial_sort .= "[$i, 0]";
                else if ($column['sort'] == "desc")
                    $initial_sort .= "[$i, 1]";
            }
            $i++;
        }
        $initial_sort .= "]";  
        
        // Render header rows
        $result = "
        <div class='table-responsive'>
            <table class='tablesorter table table-bordered table-hover table-striped tablesorter-bootstrap' data-sortlist='$initial_sort'>
                <thead><tr>";
        foreach($this->_columns as $column_name => $column) {
            if (isset($column['sorter'])){
                $sorter = 'sorter-' . $column['sorter'];
            } else {
                $sorter = "";
            }
        
            $result .= "<th class=$sorter>{$column['label']} <i class='fa fa-sort'></i></th>";
        }
        
        // Add menu items column, if specified
        if (!empty($this->_menu_items)){
            $result .= "<th>{$this->_menu_label} <i class='fa fa-sort'></i></th>";
        }
        
        $result .= "</tr></thead><tbody>";
        
        // Render data rows
        foreach ($this->_rows as $row_id => $row) {
            // Render rows
           $result .= $this->renderRow($row_id);
        }
        
        // Close table
        $result .= "</tbody></table>";
        
        // Render paging controls
        $result .= "
            <div class='pager pager-lg'>
                <span class='pager-control first' title='First page'><i class='fa fa-angle-double-left'></i></span>
                <span class='pager-control prev' title='Previous page'><i class='fa fa-angle-left'></i></span>
                <span class='pagedisplay'></span> <!-- this can be any element, including an input -->
                <span class='pager-control next' title='Next page'><i class='fa fa-angle-right'></i></span>
                <span class='pager-control last' title= 'Last page'><i class='fa fa-angle-double-right'></i></span>
                <br><br>
                Jump to Page: <select class='gotoPage'></select>
                &bull; Show: <select class='pagesize'>
                    <option value='2'>2</option>
                    <option value='5'>5</option>
                    <option value='10'>10</option>
                    <option value='100'>100</option>
                </select>
            </div>";
        
        
        return $result;
    }

    private function renderRow($row_id){
        $row = $this->_rows[$row_id];
    
        $result = "<tr>";
         foreach($this->_columns as $column_name => $column) {
             $result .= $this->renderCell($row_id, $column_name);
         }
         
         // Build menu
         if (!empty($this->_menu_items)){
             $result .= $this->renderRowMenu($row_id);
         }  
        // Close row
        $result .= "</tr>";
         
        return $result;
    }
    
    
    private function renderRowMenu($row_id){
        $row = $this->_rows[$row_id];
        if (isset($row[$this->_menu_style_field])){
            $row_style = $row[$this->_menu_style_field];
        } else {
            $row_style = "primary";
        }
        if (isset($row[$this->_menu_state_field])){
            $row_state = $row[$this->_menu_state_field];
        } else {
            $row_state = "Options";
        }
        
        $result = "
            <td>
                <div class='btn-group'>
                    <button type='button' class='btn btn-$row_style dropdown-toggle' data-toggle='dropdown'>
                      $row_state <span class='caret'></span>
                    </button>
                    <ul class='dropdown-menu' role='menu'>";
        foreach ($this->_menu_items as $item_name => $item) {
            if (isset($item['template']))
                $item_template = $item['template'];
            else
                continue;
            $result .= "<li>" . $this->renderString($row, $item_template) . "</li>";      
        }                     
                    
        $result .= "</ul>
                </div>
            </td>";     
    
        return $result;
    }
    
    private function renderCell($row_id, $column_name){
        $row = $this->_rows[$row_id];
        $column = $this->_columns[$column_name];
        $template = isset($column['template']) ? $column['template'] : "";
        $empty_field = isset($column['empty_field']) ? $column['empty_field'] : null;
        $empty_value = isset($column['empty_value']) ? $column['empty_value'] : null;
        $empty_template = isset($column['empty_template']) ? $column['empty_template'] : "";
        $sorter = isset($column['sorter']) ? $column['sorter'] : null;
        $sort_field = isset($column['sort_field']) ? $column['sort_field'] : null;

        $td = "<td>";
        // If a sort_field is set, construct the appropriate metadata td
        if ($sorter && $sort_field && isset($row[$sort_field])){
            if ($sorter == "metanum"){
                $td = "<td data-num='{$row[$sort_field]}'>";
            } else if ($sorter == "metatext"){
                $td = "<td data-text='{$row[$sort_field]}'>";
            } else {
                $td = "<td>";       // Default will be empty
            }
        }
        
        // If an empty_field name was specified, and its value matches the "empty value", render the empty template 
        if ($empty_field && ($row[$empty_field] == $empty_value)){
            return $td . $this->renderString($row, $empty_template) . "</td>";
        } else {
            return $td . $this->renderString($row, $template) . "</td>";
        }
    }    
        
    private function renderString($row, $template){
        $result = $template;
  
        // First, replace any arrays (format: [[array_name template]])
        if (preg_match_all("/\[\[([a-zA-Z_0-9]*)\s(.+?)\]\]/", $result, $matches)){
            // Iterate through each array template that was matched
            for ($i=0; $i<count($matches[0]); $i++){
                if (!isset($matches[1]) || !isset($matches[1][$i]))
                    continue;
                if (!isset($matches[2]) || !isset($matches[2][$i]))
                    continue;
                // Get array name and template
                $array_name = $matches[1][$i];
                $array_template = $matches[2][$i];
                //error_log($array_name . ":" . $array_template);
                // Check that array name exists in $row and is of type 'array' 
                if (!isset($row[$array_name]) || gettype($row[$array_name]) != "array")
                    continue;
                //error_log("$array_name is a valid array element");
                // Construct the rendered array template
                $array_result = "";
                // This loop iterates over the elements in the array
                foreach ($row[$array_name] as $array_idx => $array_el){
                    // Each element in the array must itself be an array
                    if (gettype($array_el) != "array")
                        continue;
                    //error_log("Replacing hooks in $array_template");
                    $array_instance = replaceKeyHooks($array_el, $array_template);
                    // Append the array instance to the overall array result
                    $array_result .= $array_instance;
                }
                
                // Ok, now replace the entire array placeholder with the rendered array
                $result = str_replace($matches[0][$i], $array_result, $result);
            }

        }
        // Then, replace all remaining scalar values
        $result = replaceKeyHooks($row, $result);
        return $result;
    }
}
