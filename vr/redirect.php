<!doctype html>
<html >
  <head>
    <script>
      window.opener.oauth = <?php
echo json_encode(array(
  'status'=> 1,
  'code'=>$_GET['code']
));
      ?>;
      self.close ();
    </script>
  </head>
  <body>

</html>