<?php
# Cabera de la vista de configuracion del plugin
$_['heading_title'] = 'ComproPago (Oxxo, 7Eleven, Copel ...)';

# Texto en el segundo nivel de los breadcrums en la cabecera de configuracion
$_['text_payment'] = 'Payments';

# Texto de estatus en el listado de modulos
$_['text_enabled'] = 'Enabled';
$_['text_disabled'] = 'Disabled';

# Texto cabecera de seccion
$_['text_edit'] = 'Edit Compropago';

# Texto que aparece dentro de la segunda columna en el listado modulos - prefijo text_mymodulo
$_['text_compropago'] = '<a href="http://www.compropago.com/documentacion/plugins" target="_blank"><img src="view/image/payment/compropago.png" alt="ComproPago" title="ComproPago" style="border: 1px solid #EEEEEE;" /></a>';

# Texto desplegado de cofiguracion exitosa en el listado de modulos
$_['text_success'] = 'ComproPago Plugin has been configurated.';

# Texto de inputs de configuracion;
$_['entry_secret_key'] = 'Secret Key';
$_['entry_public_key'] = 'Public Key';
$_['entry_mode'] = 'Active Mode';

$_['entry_select_mode_true'] = 'Yes';
$_['entry_select_mode_false'] = 'No';

$_['entry_order_status_new'] = 'New Order Status';
$_['entry_order_status_approve'] = 'Approve Order Status';
#$_['entry_order_status_pending'] = 'Pending Order Status';
#$_['entry_order_status_declined'] = 'Declined Order Status';
#$_['entry_order_status_cancel'] = 'Cancel or Expired Order Status';
$_['entry_sort_order']	= 'Sort Order';
$_['entry_status'] = 'Status';

#$_['entry_db_prefix'] = "Data Tables Prefix";

$_['entry_showlogo'] = "Show Logos";
$_['entry_description'] = "Description Service";
$_['entry_instrucciones'] = "Instructions";

/**
 * Tab titles in configuration view
 */
$_['tab_plugin_configurations'] = "Plugin Configurations";
$_['tab_display_configurations'] = "Display Configurations";
$_['tab_estatus_configurations'] = "Estatus Configurations";


# Tooltips de ayuda dentro la configuracion  del plugin
$_['help_secret_key'] = 'The private key you get in the settings option the ComproPago platform. www.compropago.com';
$_['help_public_key'] = 'The public key you get in the settings option the ComproPago platform. www.compropago.com';
$_['help_mode'] = 'Are you test? select = No';

#$_['help_db_prefix'] = "Example Prefix=oc_ => oc_compropago_transaction";


// Error
$_['error_secret_key'] = 'Secret Key Required!';
$_['error_public_key'] = 'Public Key Required!';

# Texto tooltip en los botones de configuracion
$_['text_button_save'] = 'Save';
$_['text_button_cancel'] = 'Cancel';
?>