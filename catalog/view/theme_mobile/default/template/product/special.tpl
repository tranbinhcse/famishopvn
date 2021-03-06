<?php echo $header; ?>
<?php
/*CHECK COOKIE LISTINGTYPE*/	
if(isset($_COOKIE["listingType"])) $listingType = $_COOKIE["listingType"];
else $listingType =  isset($data['product_catalog_mode']) && $data['product_catalog_mode'] ? 'list' : 'grid';
?>
<div class="container">
	
	<div class="row">
		<?php echo $column_left; ?>
	    <?php if ($column_left && $column_right) { ?>
	    <?php $class = 'col-sm-6'; ?>
	    <?php } elseif ($column_left || $column_right) { ?>
	    <?php $class = 'col-sm-8 col-md-9 col-xs-12'; ?>
	    <?php } else { ?>
	    <?php $class = 'col-sm-12'; ?>
	    <?php } ?>
		
	    <div id="content" class="<?php echo $class; ?>">
			<?php echo $content_top; ?>
	        
			<div class="products-category">
			<?php if ($products) { ?>
				<!-- Filters -->
				<div class="product-filter filters-panel clearfix">
					<div class="col-xs-4 view-mode ">
						<div class="view-mode">
							<div class="list-view">
								<button class="btn btn-default grid <?php if($listingType =='grid') { echo 'active'; } ?>"   title="<?php echo $button_grid; ?>"><i class="fa fa-th-large"></i></button>
								<button class="btn btn-default list <?php if($listingType =='list') { echo 'active'; } ?>"   title="<?php echo $button_list; ?>"><i class="fa fa-bars"></i></button>
							</div>
						</div>
					</div>
					
					<div class="short-by-show col-xs-8">
						<div class="form-group short-by">
							<i class="fa fa-sort-amount-asc" ></i>
							<select id="input-sort" class="form-control" onchange="location = this.value;">
							  <?php foreach ($sorts as $sorts2) { ?>
							  <?php if ($sorts2['value'] == $sort . '-' . $order) { ?>
							  <option value="<?php echo $sorts2['href']; ?>" selected="selected"><?php echo $sorts2['text']; ?></option>
							  <?php } else { ?>
							  <option value="<?php echo $sorts2['href']; ?>"><?php echo $sorts2['text']; ?></option>
							  <?php } ?>
							  <?php } ?>
							</select>
						</div>
					</div>
				</div>
				<!-- //end Filters -->
				
				<!--Changed Listings-->
				<?php 
					if (file_exists(DIR_TEMPLATE. 'default/template/soconfig/listing.php')) include(DIR_TEMPLATE.'default/template/soconfig/listing.php');
					else echo 'Not found';
				?>
				<!--// End Changed listings-->
				
				<!-- Filters -->
				<div class="product-filter filters-panel text-center clearfix">
				   <div class="short-by-show">
						<p><?php echo $results; ?></p>
					</div>
					<?php if (!empty($pagination)){?>
						<div class="box-pagination "><?php echo $pagination; ?></div>
					<?php }?>
					
				</div>
				<!-- //end Filters -->
			
			<?php } ?>
		    </div>
		</div>	 
		
		<script type="text/javascript"><!--
		 $('.view-mode .list-view button').bind("click", function() {
			if ($(this).is(".active")) {return false;}
			$.cookie('listingType', $(this).is(".grid") ? 'grid' : 'list', { path: '/' });
			location.reload();
		});
		//-->
		</script> 
		<?php echo $content_bottom; ?>
		<?php echo $column_right; ?>
		
	</div>
</div>
<?php echo $footer; ?>

