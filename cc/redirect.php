<!doctype html>
<html >
  <head>
    <script>
      var queryStr = window.location.hash.substr(1),
        queryArr = queryStr.split('&'),
        queryParams = [];
      for (var q = 0, qArrLength = queryArr.length; q < qArrLength; q++) {
        var qArr = queryArr[q].split('=');
        queryParams[qArr[0]] = qArr[1];
      }
     window.opener.oauth = {status:1,code:queryParams.access_token}
      self.close ();
    </script>
  </head>
  <body>

</html>