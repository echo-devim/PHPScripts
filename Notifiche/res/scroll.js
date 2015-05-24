 $(document).ready(function() {
 $('.mostra_notifiche').css('cursor', 'pointer');
 $('.nascondi_notifiche').css('cursor', 'pointer');
 $('.elimina_notifica').css('cursor', 'pointer');
$('.mostra_notifiche').click(function() {
	$('.avviso_notifiche').hide();
	$('.notifiche').slideDown('slow', function() { /*fine*/ });
	});
$('.nascondi_notifiche').click(function() {
	$('.notifiche').hide();
	$('.avviso_notifiche').show();
	});
});

function delete_all() {
	var esito = confirm('Sei sicuro di voler cancellare tutte le notifiche?');
	if (esito == true)
		document.location.href="?elimina_notifica=all";
}