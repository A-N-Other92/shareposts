<?php require APPROOT . '/views/inc/header.php'; ?>
  <?php flash('post_message'); ?>
  <div class="row mb-3">
    <div class="col-md-6">
    <h1>Posts</h1>
    </div>
    <div class="col-md-6">
      <a class="btn btn-primary pull-right" href="<?php echo URLROOT; ?>/posts/add"><i class="fa fa-pencil" aria-hidden="true"></i> Add Post</a>
    </div>
  </div>
  <?php foreach($data['posts'] as $post) : ?>
    <div class="card card-body mb-3">
      <h4 class="card-title"><?php echo $post->title; ?></h4>
      <div class="bg-light p-2 mb-3">
        Written by <?php echo $post->name; ?> on <?php echo date_format(new DateTime($post->postCreatedAt),"jS M y  :  g i a");  ?>
      </div>
        <p class="card-text"><?php if(strlen($post->body) > 200) {
                 echo substr($post->body,0,250) . ' ............'; } 
             else { 
                 echo $post->body; 
             }  ?></p>
          <a class="btn btn-dark" href="<?php echo URLROOT; ?>/posts/show/<?php echo $post->postId; ?>/<?php echo $data['pageStart']; ?>">More</a>
    </div>
  <?php endforeach; ?>

  <?php  if($data['recCount'] > POST_DISPLAY)  { // More than 1 page.
		$pages = ceil ($data['recCount']/POST_DISPLAY);
	} else {
		$pages = 1;
	}
   
  if ($pages > 1) {
     $current_page = ($data['pageStart']/POST_DISPLAY) + 1;

     // Make all the numbered pages:
  echo '<nav>';
  echo '<ul class="pagination">';
  if ($data['pageStart'] != 0) {
     echo '<li class="page-item"><a class="page-link" href="' . URLROOT . '/posts/index/' . (($data['pageStart']) - POST_DISPLAY) . '">' . '<<' . '</a></li>';
  }  else {
    echo '<li class="page-item disabled"><a class="page-link"><<</a></li>';
  }
	      for ($i = 1; $i <= $pages; $i++) {
		       if ($i != $current_page) {
             echo '<li class="page-item"><a class="page-link" href="' . URLROOT . '/posts/index/' . ($i -1) * POST_DISPLAY . '">' . $i . '</a></li>';
		       }   else {
			     echo '<li class="page-item active"><a class="page-link">' . $i . '</a></li>';
		       }
     } // End of

     if ($current_page != $pages) {
      echo '<li class="page-item"><a class="page-link" href="' . URLROOT . '/posts/index/' . (($data['pageStart']) + POST_DISPLAY) . '">' . '>>' . '</a></li>';
   }  else {
     echo '<li class="page-item disabled"><a class="page-link">>></a></li>';
   }

  echo '</ul>';
  echo '</nav>';
  }  

  ?>
<?php require APPROOT . '/views/inc/footer.php'; ?>