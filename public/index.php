<html>
<body>
  <link rel="stylesheet" type="text/css" href="smart_green.css">

  <form class="smart-green">
    <h1>Manage the news</h1>
    <div id="enter">
      <input type="radio" name="action"value="add" checked> Add
      <input type="radio" name="action"value="edit"> Edit
      <div id="input">
        <label style="display: none;">
          ID:<input type="number" name="news_id" readonly>
          <button type='button'>selector</button>
        </label>
        <label>
          Date: <input type="date" name="news_date">
        </label>
        <label>
          Link: http://www.iptp.net/{lang}/
          <input type="text" style="width:40%" name="news_link">
          <button type="button" id="edit">edit</button>
        </label>
        <label>
           news_ru: <textarea  name="news_ru" cols="35"></textarea>
         </label>
         <label>
           news_eng: <textarea  name="news_eng" cols="35"></textarea>
         </label>
         <label>
           news_cn: <textarea  name="news_cn" cols="35"></textarea>
         </label>
         <label>
           news_hk: <textarea  name="news_hk" cols="35"></textarea>
         </label>
      </div>
      <button class='sumbit' type='button'>Sumbit</button>
    </div>
    <div id="select" hidden="true">
      <button type='button'>back</button>
      <div align="right">
        <a href="#"><img src="/pics/ruflag48.png" alt="ru" border="0" height="26" width="28"></a>
  		  <a href="#"><img src="/pics/cnflag48.png" alt="cn" border="0" height="26" width="28"></a>
        <a href="#"><img src="/pics/hkflag48.png" alt="hk" border="0" height="26" width="28"></a>
        <a href="#"><img src="/pics/ukflag48.png" alt="en" border="0" height="26" width="28" ></a>
      </div>
      <div id="option">

      </div>

    </div>
  </form>
  <a style="display:scroll;position:fixed;bottom:20px;right:20px;" href="#" title="" onFocus="if(this.blur)this.blur()">
  TOP</a>

</body>
</html>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script>
$(document).ready(function(){
    function validInput(){
      if (//!$("#input input[name='news_id']").val() ||
      !$("#input input[name='news_date']").val() ||
      !$("#input input[name='news_link']").val() ||
      !$("#input textarea[name='news_ru']").val() ||
      !$("#input textarea[name='news_eng']").val() ||
      !$("#input textarea[name='news_hk']").val() ||
      !$("#input textarea[name='news_cn']").val()){
        alert("contain empty inputs");
        return false;
      }
      return true;
    }
    $("#edit").click(function() { //go to the event edit page
      var theForm, newInput1, newInput2;
      // Start by creating a <form>
      theForm = document.createElement('form');
      theForm.action = 'eeditor.php';
      theForm.method = 'post';
      // Next create the <input>s in the form and give them names and values
      newInput1 = document.createElement('input');
      newInput1.type = 'hidden';
      newInput1.name = 'news_id';
      newInput1.value = $("#input input[name='news_id']").val();
      newInput2 = document.createElement('input');
      newInput2.type = 'hidden';
      newInput2.name = 'news_link';
      newInput2.value = $("#input input[name='news_link']").val();
      // Now put everything together...
      theForm.appendChild(newInput1);
      theForm.appendChild(newInput2);
      // ...and it to the DOM...
      //document.appendChild(theForm);
      // ...and submit it
      theForm.submit();
    });
    $(":radio").change(function(){  //change between add or edit
          $("#input input").val("");
          $("#input textarea").val("");
          $("input[name=news_id]").parent().toggle();
    });

    $(".sumbit").click(function(){
      if (validInput())
      if($('input[name="news_id"]').val()){ //contains id => update
        $.ajax({
          url: "update.php",
          type: "POST",
          async: true,
          data: { news_id:$('input[name="news_id"]').val(),
                  news_date:$('input[name="news_date"]').val(),
                  news_link:$('input[name="news_link"]').val(),
                  news_ru:$('textarea[name="news_ru"]').val(),
                  news_eng:$('textarea[name="news_eng"]').val(),
                  news_cn:$('textarea[name="news_cn"]').val(),
                  news_hk:$('textarea[name="news_hk"]').val(),
                  },
          dataType: "html",

          success: function(data){
            alert(data);
          }
        });
      }
      else {
        $.ajax({
          url: "php_form.php",
          type: "POST",
          async: true,
          data: { news_date:$('input[name="news_date"]').val(),
                  news_link:$('input[name="news_link"]').val(),
                  news_ru:$('textarea[name="news_ru"]').val(),
                  news_eng:$('textarea[name="news_eng"]').val(),
                  news_cn:$('textarea[name="news_cn"]').val(),
                  news_hk:$('textarea[name="news_hk"]').val(),
                  },
          dataType: "html",

          success: function(data){
            alert(data);
            $("#input input").val("");
            $("#input textarea").val("");
          }
        });
      }
    });

    $("#enter button:eq(0)").click(function(){
      $("form > div").toggle();
      $.ajax({
        url: "select.php",
        type: "GET",
        async: true,
        data: {lang:"en"},
        dataType: "html",

        success: function(data){
          $("div[id='option']").html(data);
        }
      });

    });

    $("#select a").click(function(){
      $.ajax({
        url: "select.php",
        type: "GET",
        async: true,
        data: {lang:$("img",this).attr("alt")},
        dataType: "html",

        success: function(data){
          $("div[id='option']").html(data);
        }
      });
    });

    $("#select button:eq(0)").click(function(){
      $("form > div").toggle();
    });

    $(document).on("click","div button[name='news_id']",function(){
      $.ajax({
        url: "edit.php",
        type: "POST",
        async: true,
        data: { news_id:$(this).val() },
        dataType: "json",

        success: function(data){
          $("form > div").toggle();
          $("#input input[name='news_id']").val(data.id);
          $("#input input[name='news_date']").val(data.date);
          $("#input input[name='news_link']").val(data.link);
          $("#input textarea[name='news_ru']").val(data.ru);
          $("#input textarea[name='news_eng']").val(data.en);
          $("#input textarea[name='news_hk']").val(data.hk);
          $("#input textarea[name='news_cn']").val(data.cn);
        }
      });
    });
});

</script>
