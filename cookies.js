module.exports = function (cookies){
  if (!cookies) return {};
  var obj = {};
  cookies.split(';').forEach(function(e){
    var parts = e.split('=');
    obj[parts[0].trim()] = parts[1].trim();
  });
  return obj;
}
