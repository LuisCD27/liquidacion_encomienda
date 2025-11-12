<?php
function qr_gen($text, $outfile){
  $url = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($text);
  $context = stream_context_create(['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false]]);
  $img = @file_get_contents($url, false, $context);
  if($img){ file_put_contents($outfile, $img); return $outfile; }
  return null;
}
?>
