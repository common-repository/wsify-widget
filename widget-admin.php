<?php
if(isset($_POST["wsify_widget_account_secret"]) && $_POST["wsify_widget_account_secret"] != ""){
  update_user_meta(get_current_user_id(), "user_wsify_account_secret", $_POST["wsify_widget_account_secret"]);
  update_option( 'wsify_site_admin_id', get_current_user_id() );
}
// delete_user_meta(get_current_user_id(), "wsify_widget_username");
$wsify_widget_account_secret = get_user_meta(get_current_user_id(), "user_wsify_account_secret", true);
?>

<!-- Bootstrap 3.3.5 -->
<link rel="stylesheet" href="<?php echo plugins_url( 'wsify-widget/css/bootstrap/css/bootstrap.min.css' ); ?>">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<!-- Ionicons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="<?php echo plugins_url( 'wsify-widget/css/AdminLTE.min.css' ); ?>">

<div class="register-box">
  <div class="register-logo">
    <a href="https://www.wsify.com/" target="_blank"><img src="<?php echo plugins_url( 'wsify-widget/images/logo_wsify.png' ); ?>" width="200" /></a>
  </div>

  <div class="register-box-body">
    <p class="login-box-msg">Widget Configuration</p>
    <form id="wsify_login_config" class="ui form" role="configuration" action="#" method="POST">

      <?php if(isset($_POST["wsify_widget_account_secret"]) && $_POST["wsify_widget_account_secret"] != 0 && $_POST["wsify_widget_account_secret"] != ""){ ?>
      <div class="alert alert-success fade in <?php if($return_message!="Yes"){ echo "hidden"; } ?>">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        Your information saved successfully.
      </div>
      <?php } ?>

      <div class="form-group has-feedback">
        <input type="text" class="form-control" name="wsify_widget_account_secret" id="wsify_widget_account_secret" placeholder="Please enter account secret" value="<?php echo $wsify_widget_account_secret; ?>">
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <p>You can find <strong>Account Secret</strong> in your WSIFY account settings page.</p>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <button id="wsify_status" type="submit" class="btn btn-primary btn-block btn-flat">Connect</button>
          <span id="wsify_status_checking" style="display: none;"></span>
        </div><!-- /.col -->
      </div>
    </form>
    <?php if($wsify_widget_account_secret == ""){ ?>
    <div class="social-auth-links text-center">
      <p>- OR -</p>
      <a href="https://www.wsify.com/register" target="_blank" class="text-center">New Member Registration</a>
    </div>
    <?php } ?>
  </div><!-- /.form-box -->
  <div class="clearfix"></div>
</div><!-- /.register-box -->

<?php //} ?>

<!-- Bootstrap 3.3.5 -->
<script src="<?php echo plugins_url( 'wsify-widget/css/bootstrap/js/bootstrap.min.js' ); ?>"></script>
