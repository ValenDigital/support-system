<?php

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class MU_Support_Ticket_Categories_Table extends WP_List_Table {

    private $data;

	function __construct(){
        //Set parent defaults
        parent::__construct( array(
            'singular'  => __( 'Category', INCSUB_SUPPORT_LANG_DOMAIN ),  
            'plural'    => __( 'Categories', INCSUB_SUPPORT_LANG_DOMAIN ), 
            'ajax'      => false        
        ) );
        
    }

    function column_default( $item, $column_name ){

        $value = '';
    	switch ( $column_name ) {
            default		: $value = $item[ $column_name ]; break;
    	}
        return $value;
    }


    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'id'        => __( 'ID', INCSUB_SUPPORT_LANG_DOMAIN ),
            'name'      => __( 'Name', INCSUB_SUPPORT_LANG_DOMAIN ),
            'tickets'   => __( 'Tickets', INCSUB_SUPPORT_LANG_DOMAIN )
        );
        return $columns;
    }

    function column_cb($item){
        if ( '0' == $item['defcat'] ) {
            return sprintf(
                '<input type="checkbox" name="%1$s[]" value="%2$s" />',
                $this->_args['singular'],
                $item['cat_id']
            );
        }
        else {
            return '';
        }
    }

    function column_id( $item ) {
        return $item['cat_id'];
    }

    function column_name( $item ) {
        if ( '1' == $item['defcat'] )
            return $item['cat_name'] . ' <strong>' . __( '[Default category]', INCSUB_SUPPORT_LANG_DOMAIN ) . '</strong>';
        else
            return $item['cat_name'];
    }


    function column_tickets( $item ) {
        if( ! $item['tickets'] ) {
            return $item['tickets'];
        }
        else {
            $link = MU_Support_System::$network_main_menu->get_permalink();
            $link = add_query_arg( 'category', $item['cat_id'], $link );
            return '<a href="' . $link . '">' . $item['tickets'] . '</a>';
        }
    }

    function get_bulk_actions() {
        $actions = array(
            'delete'    => __( 'Delete', INCSUB_SUPPORT_LANG_DOMAIN )
        );
        return $actions;
    }

 

    function prepare_items() {

        $model = MU_Support_System_Model::get_instance();

        if( 'delete' === $this->current_action() ) {
            if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'bulk-' . $this->_args['plural'] ) )
                wp_die( 'Security check error', INCSUB_SUPPORT_LANG_DOMAIN );

            if ( isset( $_POST['category'] ) && is_array( $_POST['category'] ) ) {
                foreach ( $_POST['category'] as $category )
                    $model->delete_ticket_category( absint( $category ) );
            }
        }

    	$per_page = 7;

    	$columns = $this->get_columns();
        $hidden = array( 'id' );
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array(
        	$columns, 
        	$hidden, 
        	$sortable
        );

        $current_page = $this->get_pagenum();

        
        $data = $model->get_ticket_categories();

        $total_items = count( $data );

        $data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
        

        foreach ( $data as $key => $item ) {
            $data[ $key ]['tickets'] = $model->get_tickets_from_cat( $item['cat_id'] );
        }

        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,                
            'per_page'    => $per_page,                   
            'total_pages' => ceil($total_items/$per_page) 
        ) );

    }

}
?>