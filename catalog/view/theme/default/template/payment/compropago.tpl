<link rel="stylesheet" href="catalog/view/theme/default/stylesheet/compropago/cp-style.css">

<form class="form-horizontal">
    <fieldset id="payment">

        <section class="cpcontainer cpprovider-select">
            <div class="cprow">
                <div class="cpcolumn">
                    <h3><?php echo $comprodata['description']; ?></h3>
                </div>
            </div>

            <div class="cprow">
                <div class="cpcolumn">
                    <?php echo $comprodata['instrucciones']; ?> <br>
                </div>
            </div>

            <div class="cprow">
                <div class="cpcolumn">
                    <?php if($comprodata['showlogo'] == 'yes') { ?>

                        <ul class="providers_list">
                            <?php foreach ($comprodata['providers'] as $provider){ ?>
                                <li>
                                    <input type="radio" id="cp_<?php echo $provider->internal_name; ?>" name="compropagoProvider" value="<?php echo $provider->internal_name; ?>">
                                    <label class="cp-provider" id="cp_l_<?php echo $provider->internal_name; ?>" for="cp_<?php echo $provider->internal_name; ?>">
                                        <img src="<?php echo $provider->image_medium; ?>" alt="<?php echo $provider->internal_name; ?>">
                                    </label>
                                </li>
                            <?php } ?>
                        </ul>

                    <?php } else { ?>

                        <div id="cppayment_store">
                            <select name="compropagoProvider" class="providers_list" title="Proveedores">
                                <?php foreach ($comprodata['providers'] as $provider){ ?>
                                    <option value="<?php echo $provider->internal_name; ?>"> <?php echo $provider->name; ?> </option>
                                <?php } ?>
                            </select>
                        </div>

                    <?php } ?>
                </div>
            </div>

        </section>


        <script>
            
            var providers = document.querySelectorAll(
                    ".cpcontainer.cpprovider-select ul li label img"
            );

            for (x = 0; x < providers.length; x++){
                providers[x].addEventListener('click', function(){
                    resetOpacity();
                    this.parentNode.style.opacity = 1;
                });
            }
            
            function resetOpacity(){
                for(x = 0; x < providers.length; x++){ providers[x].parentNode.style.opacity = 0.6; }
            }
        </script>
        
        
    </fieldset>
</form>

<div class="buttons">
    <div class="pull-right">
        <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary" />
    </div>
</div>

<script type="text/javascript">
    
    $('#button-confirm').bind('click', function() {
        var internal = $("input[name=compropagoProvider]:checked").val();
        
        $.ajax({
            url: 'index.php?route=payment/compropago/send',
            type: 'post',
            data: {compropagoProvider: internal},
            dataType: 'json',
            beforeSend: function() {
                $('#button-confirm').button('loading');
            },
            complete: function() {
                $('#button-confirm').button('reset');
            },
            success: function(json) {
                if (json['error']) {
                    alert(json['error']);
                }

                if (json['success']) {
                    location = json['success'];
                }
            }
        });
    });
    
</script>
