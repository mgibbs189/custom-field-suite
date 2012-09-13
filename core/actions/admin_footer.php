<style type="text/css">
.tablenav {
    display: none;
}

p.search-box {
    display: none;
}

.row-actions .inline {
    display: none;
}

#screen-options-link-wrap {
    display: none;
}

#posts-filter {
    margin-right: 300px;
}

.posts-sidebar {
    position: absolute;
    right: 0px;
    width: 275px;
    margin: 28px 20px 0 0;
}

#poststuff .inside {
    margin: 0;
    padding: 0;
}

#poststuff .inside h4,
#poststuff .inside p {
    margin: 3px 0;
    padding: 0;
}

.posts-sidebar .inside .field {
    border-bottom: 1px solid #dfdfdf;
    border-top: 1px solid #fff;
    padding: 6px 10px;
}

.posts-sidebar .inside .field:last-child {
    border-bottom:  none;
}

.btn {
  display: inline-block;
  *display: inline;
  padding: 4px 14px;
  margin-bottom: 0;
  *margin-left: .3em;
  font-size: 14px;
  line-height: 20px;
  *line-height: 20px;
  color: #333333;
  text-align: center;
  text-decoration: none;
  text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
  vertical-align: middle;
  cursor: pointer;
  background-color: #f5f5f5;
  *background-color: #e6e6e6;
  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#ffffff), to(#e6e6e6));
  background-image: -webkit-linear-gradient(top, #ffffff, #e6e6e6);
  background-image: -o-linear-gradient(top, #ffffff, #e6e6e6);
  background-image: linear-gradient(to bottom, #ffffff, #e6e6e6);
  background-image: -moz-linear-gradient(top, #ffffff, #e6e6e6);
  background-repeat: repeat-x;
  border: 1px solid #bbbbbb;
  *border: 0;
  border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
  border-color: #e6e6e6 #e6e6e6 #bfbfbf;
  border-bottom-color: #a2a2a2;
  -webkit-border-radius: 4px;
     -moz-border-radius: 4px;
          border-radius: 4px;
  filter: progid:dximagetransform.microsoft.gradient(startColorstr='#ffffffff', endColorstr='#ffe6e6e6', GradientType=0);
  filter: progid:dximagetransform.microsoft.gradient(enabled=false);
  *zoom: 1;
  -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
     -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
          box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
}

.btn:hover {
  color: #333333;
  text-decoration: none;
  background-color: #e6e6e6;
  *background-color: #d9d9d9;
  /* Buttons in IE7 don't get borders, so darken on hover */

  background-position: 0 -15px;
  -webkit-transition: background-position 0.1s linear;
     -moz-transition: background-position 0.1s linear;
       -o-transition: background-position 0.1s linear;
          transition: background-position 0.1s linear;
}

.btn {
  border-color: #c5c5c5;
  border-color: rgba(0, 0, 0, 0.15) rgba(0, 0, 0, 0.15) rgba(0, 0, 0, 0.25);
}

.btn-success {
  color: #ffffff;
  text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
  background-color: #5bb75b;
  *background-color: #51a351;
  background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#62c462), to(#51a351));
  background-image: -webkit-linear-gradient(top, #62c462, #51a351);
  background-image: -o-linear-gradient(top, #62c462, #51a351);
  background-image: linear-gradient(to bottom, #62c462, #51a351);
  background-image: -moz-linear-gradient(top, #62c462, #51a351);
  background-repeat: repeat-x;
  border-color: #51a351 #51a351 #387038;
  border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
  filter: progid:dximagetransform.microsoft.gradient(startColorstr='#ff62c462', endColorstr='#ff51a351', GradientType=0);
  filter: progid:dximagetransform.microsoft.gradient(enabled=false);
}

.btn-success:hover,
.btn-success:active {
  color: #ffffff;
  background-color: #51a351;
  *background-color: #499249;
}
</style>

<script>
(function($) {
    $(function() {
        $('.wp-list-table').before($('#posts-sidebar-box').html());
    });
})(jQuery);
</script>

<div id="posts-sidebar-box" class="hidden">
    <div class="posts-sidebar" id="poststuff">
        <div class="postbox">
            <div class="handlediv"><br></div>
            <h3 class="hndle"><span><?php _e('Custom Field Suite', 'cfs'); ?> <?php echo $this->version; ?></span></h3>
            <div class="inside">
                <div class="field">
                    <p>
                        <a href="https://uproot.us/custom-field-suite/changelog/" target="_blank"><?php _e('Changelog', 'cfs'); ?></a> &nbsp; | &nbsp;
                        <a href="https://uproot.us/custom-field-suite/documentation/" target="_blank"><?php _e('User Guide', 'cfs'); ?></a> &nbsp; | &nbsp;
                        <a href="https://uproot.us/" target="_blank"><?php _e('Website', 'cfs'); ?></a>
                    </p>
                </div>
                <div class="field">
                    <p>Please support the plugin by <a href="http://wordpress.org/extend/plugins/custom-field-suite/" target="_blank">rating it on wordpress.org</a> or donating.</p>
                </div>
                <div class="field">
                    <p>
                        <a class="btn btn-success" href="https:uproot.us/contributors/" target="_blank">Donate Now</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
