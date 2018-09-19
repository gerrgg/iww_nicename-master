<?php
/*
Plugin Name: iWantWorkwear Nicename
Plugin URI:
Description: Allows the user to make big changes.
Version: 0.3.0
Author: iWantWorkwear
Author URI: http://www.ur54.com
*/
ini_set( 'memory_limit', '1024M' );
// memory leak?
add_action( 'admin_menu', 'iww_nicename_menu', 1 );
// must call menu before iww_enqueue_scripts

function iww_enqueue_scripts(){
}

function iww_nicename_menu(){
  add_submenu_page( 'edit.php?post_type=product', __('Product Nicenames', 'iww'), __('Nicenames', 'iww'), 'administrator', 'iww_nicename', 'nicename_menu');
}

function nicename_menu(){

  if ( !current_user_can( 'manage_product_terms' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	?>
  <div class="container-fluid">
    <form method="POST" action="<?php echo admin_url( 'admin-post.php' );?>">
      <input type="hidden" name="action" value="optimize_products">
      <input type="submit" name="submit" value="Google Shopping Feed">
    </form>
    <form method="POST" action="<?php echo admin_url( 'admin-post.php' );?>">
      <input type="hidden" name="action" value="match_sku">
      <input type="submit" name="submit" value="Match SKU / See Missing MPN">
    </form>
    <form method="POST" action="<?php echo admin_url( 'admin-post.php' );?>">
      <input type="hidden" name="action" value="fix_dims">
      <input type="submit" name="submit" value="Fix Dimensions">
    </form>
    <form method="POST" action="<?php echo admin_url( 'admin-post.php' );?>">
      <input type="hidden" name="action" value="set_price_breaks">
      <input type="submit" name="submit" value="Set Price Breaks">
    </form>
    <form method="POST" action="<?php echo admin_url( 'admin-post.php' );?>">
      <input type="hidden" name="action" value="sync_vendors">
      <label>Vendor</label>
      <select name="vendor">
        <option value="helly hansen" selected>Helly Hansen</option>
      </select>
      <label>URL</label>
      <input type="url" name="url" required/>
      <input type="submit" name="submit" value="sync_vendors">
    </form>
    <?php
    // $list = iww_get_data();
    // nice_list( $list );
    ?>

  </div>
  <?php
}

add_action( 'admin_post_optimize_products', 'update_products', 1 );
add_action( 'admin_post_match_sku', 'update_sku', 1 );
add_action( 'admin_post_fix_dims', 'iww_fix_dims', 1 );
add_action( 'admin_post_set_price_breaks', 'iww_set_price_breaks', 1 );
add_action( 'admin_post_sync_vendors', 'iww_nice_sync_vendors', 1 );

function iww_set_price_breaks(){
  $params = array(
    'post_type' => 'product',
    'nopaging' => true,
  );
  // nopaging is a much faster way of grabbing all the products.
  $products = new WP_Query( $params );
  if( $products->have_posts() ){
    while ( $products->have_posts() ) {
  		$products->the_post();
      $product = wc_get_product( get_the_ID() );
      create_qty_breaks( $product->get_id(), $product->get_price() );
    }
  }
}

function create_qty_breaks( $id, $price ){
  if( 0 < $price && $price <= 7.5 ){
    update_post_meta( $id, '_bulkdiscount_quantity_1', 24 );
    update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
    update_post_meta( $id, '_bulkdiscount_quantity_2', 48 );
    update_post_meta( $id, '_bulkdiscount_discount_2', 10 );
    update_post_meta( $id, '_bulkdiscount_quantity_3', 72 );
    update_post_meta( $id, '_bulkdiscount_discount_3', 15 );
  } elseif( 7.5 < $price && $price <= 15 ){
    update_post_meta( $id, '_bulkdiscount_quantity_1', 12 );
    update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
    update_post_meta( $id, '_bulkdiscount_quantity_2', 24 );
    update_post_meta( $id, '_bulkdiscount_discount_2', 10 );
    update_post_meta( $id, '_bulkdiscount_quantity_3', 36 );
    update_post_meta( $id, '_bulkdiscount_discount_3', 15 );
  } elseif( 15 < $price && $price <= 60 ){
    update_post_meta( $id, '_bulkdiscount_quantity_1', 4 );
    update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
    update_post_meta( $id, '_bulkdiscount_quantity_2', 7 );
    update_post_meta( $id, '_bulkdiscount_discount_2', 10 );
    update_post_meta( $id, '_bulkdiscount_quantity_3', 12 );
    update_post_meta( $id, '_bulkdiscount_discount_3', 15 );
  } elseif( $price >= 60 ){
    update_post_meta( $id, '_bulkdiscount_quantity_1', 3 );
    update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
    update_post_meta( $id, '_bulkdiscount_quantity_2', 6 );
    update_post_meta( $id, '_bulkdiscount_discount_2', 10 );
    update_post_meta( $id, '_bulkdiscount_quantity_3', 9 );
    update_post_meta( $id, '_bulkdiscount_discount_3', 15 );
  } else {
    update_post_meta( $id, '_bulkdiscount_quantity_1', 12 );
    update_post_meta( $id, '_bulkdiscount_discount_1', 5 );
    update_post_meta( $id, '_bulkdiscount_quantity_2', 24 );
    update_post_meta( $id, '_bulkdiscount_discount_2', 10 );
    update_post_meta( $id, '_bulkdiscount_quantity_3', 36 );
    update_post_meta( $id, '_bulkdiscount_discount_3', 15 );
  }
}

function iww_fix_dims(){
  $params = array(
    'post_type' => 'product',
    'nopaging' => true,
  );
  // nopaging is a much faster way of grabbing all the products.
  $products = new WP_Query( $params );
  if( $products->have_posts() ){
    while ( $products->have_posts() ) {
  		$products->the_post();
      $product = wc_get_product( get_the_ID() );
      if( $product->get_width() == 1 ) {
        $product->set_width( '' );
        echo $product->get_name() . ' width set to ' . $product->get_width() . '<br>';
      }
      if( $product->get_length() == 1 ) {
        $product->set_length( '' );
        echo $product->get_name() . ' length set to ' . $product->get_length() . '<br>';
      }
      if( $product->get_height() == 1 ) {
        $product->set_height( '' );
        echo $product->get_name() . ' height set to ' . $product->get_height() . '<br>';
      }
      if( $product->get_weight() == 1 ) {
        $product->set_weight( '' );
        echo $product->get_name() . ' weight set to ' . $product->get_weight() . '<br>';
      }
    }
  }
}

if( !function_exists( 'get_nice_title' ) ){
	function get_nice_title( $id ){
	  $title = get_post_meta( $id, "_yoast_wpseo_title", true );
	  if( ! empty( $title ) ){
	    // if its a yoast title, remove the dynamic data stuff
	     $search_for = ['%%sep%%', '%%sitename%%', 'iWantWorkwear', '&', '?', '©', '®', '™' ];
	     $nicename = str_ireplace( $search_for, '', $title ); //value
	   } else {
	     // if there is no yoast title, grab the product title
	     $nicename = get_the_title();
	     // since titles are typically too large for GSF, cut it down
	     if (strlen($nicename) > 60 ) {
	       // wrap strings to prevent breaking words
	       $nicename = wordwrap($nicename, 60);
	       $nicename = substr($nicename, 0, strpos($nicename, "\n"));
	     }
	  }
	  return trim( $nicename );
	}
}

function nice_list( $list ){
  ?>
  <h1>Nice List: </h1>
  <table class="table table-striped table-sm">
    <thead class="thead-dark">
      <th>ID</th>
      <th>Name</th>
      <th>Description</th>
      <th>Type</th>
      <th>GTIN</th>
      <th>Age Group</th>
      <th>Gender</th>
      <th><i class="fa fa-eye"></th>
    </thead>
    <tbody>
      <?php foreach( $list as $item ) : ?>
        <tr>
          <?php foreach( $item as $attr ) : ?>
            <td>
              <?php echo $attr ?>
            </td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php
}

function add_to_list( $id, $name, $description, $post_type, $gtin, $age_group, $gender, $link ){
  $list = array(
    'id' => $id,
    'name' => $name,
    'desc' => $description,
    'type' => $post_type,
    'gtin' => $gtin,
    'age' => $age_group,
    'gender' => $gender,
    'link' => $link
  );
  return $list;
}

function make_link( $url ){
  return '<a href="' . $url . '">View</a>';
}

function update_products( ){
  $list = iww_get_data();

  $targets = array(
    'name' => 'wccaf_nice_name',
    'desc' => '_yoast_wpseo_metadesc',
    'age' => 'wccaf_age_group',
    'gender' => 'wccaf_gender',
    'gtin' => 'wccaf_gtin'
  );
  foreach( $list as $item ){
    foreach( $targets as $key => $target ){

      if( isset( $item[$key] ) || !empty( $item[$key] ) ){
        update_post_meta( $item['id'], $target, $item[$key]);
        echo $target . ' -> ' . get_post_meta( $item['id'], $target, true ) . '<br>';
      }
    }
  }
  // wp_redirect( 'http://ur54.com/wp/wp-admin/edit.php?post_type=product&page=iww_nicename' );
  // exit;
}

function iww_get_data( $method = "" ){
  $params = array(
    'post_type' => 'product',
    'nopaging' => true,
  );
  // nopaging is a much faster way of grabbing all the products.
  $products = new WP_Query( $params );
  if( $products->have_posts() ){
    while ( $products->have_posts() ) {
  		$products->the_post();
      $id = get_the_ID();

      $product = wc_get_product( $id );
      $post_type = $product->get_type();

        // I dont want to make a product just for the product type; TODO: Fix that <<

        $name = get_nice_title( $id );
        $age_group = get_post_meta( $id, "wccaf_age_group", true );
        $gender = get_post_meta( $id, "wccaf_gender", true );

        $description = get_post_meta( $id, "_yoast_wpseo_metadesc", true );
        if( empty( $description ) ){
          $description = $product->get_short_description();
        }


        if( 'variable' === $post_type ){
          // get children id
          $children = $product->get_children();

          foreach( $children as $id ){
            // each variation item as an individual product.
            $gtin = get_post_meta( $id, 'wccaf_gtin', true );
            $variation = wc_get_product( $id );
            $attr_str = wc_get_formatted_variation( $variation->get_variation_attributes(), true, false, true );
            $seo_name = $name . ' ' . $attr_str;
            $link = $variation->get_permalink();
            $list[$id] = add_to_list( $id, $seo_name, $description, $post_type, $gtin, $age_group, $gender, make_link($link) );

          }

        } else {
          // simple products.
          $link = $product->get_permalink();
          $gtin = get_post_meta( $id, 'wccaf_simple_gtin', true );
          $list[$id] = add_to_list( $id, $name, $description, $post_type, $gtin, $age_group, $gender, make_link($link) );
        }
      }
  	}
    return $list;
  }


function update_sku( ){
    add_filter( 'wc_product_has_unique_sku', '__return_false', PHP_INT_MAX );
    $args = array(
          'post_type' => 'product',
          'posts_per_page' => 1,
           'nopaging' => true,
            );
    $i=0;

    $loop = new WP_Query( $args );

    if ( $loop->have_posts() ) {
      $has_mpn = array();
      $needs_mpn = array();
        while ( $loop->have_posts() ) : $loop->the_post();
            $parent = wc_get_product($loop->post->ID);
            $children = $parent->get_children();
            foreach( $children as $child_id ){
              $child = wc_get_product( $child_id );

              $mpn = get_post_meta( $child_id, 'wccaf_mpn', true );
              if( ! empty( $mpn ) ){
                  update_post_meta( $child_id, '_sku', $mpn );
                if( $child->get_sku() === $mpn ){
                  array_push( $has_mpn, $child->get_id() . ' - successfully updated to ' . $child->get_sku() . '<br>' );
                } else {
                  array_push( $has_mpn, $child->get_id() . ' - update failed; sku still ' . $child->get_sku() . '<br>' );
                }

              } else {
                array_push( $needs_mpn, $child_id . ' - ' . $child->get_sku() . ' needs an MPN'  );
              }
            }

            $i++;

        endwhile;
        ?>
        <h1 style="text-align: center">iWantWorkwear Update SKU</h1>
        <div style="display: flex; justify-content: space-between;">
          <pre style="width: 50%">
            <h1>Has MPN</h1>
            <?php var_dump( $has_mpn ) ?>
          </pre>
          <pre style="width: 50%">
            <h1>Needs MPN</h1>
            <?php var_dump( $needs_mpn ) ?>
          </pre>
        </div>
        <?php
    } else {

        echo __( 'No products found' );

    }

    wp_reset_postdata();

}
function get_actual_id( $product ) {

  if ( $product instanceof WC_Product_Variation ) {
    return $this->get_variation_id($product);
  } else {
    return $this->get_product_id($product);
  }

}
