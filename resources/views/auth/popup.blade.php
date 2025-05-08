<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <title>IDAB-verify</title>
  </head>
  <style>
    #iframe{
      display: block;
      margin: auto;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .btn{
      display: block;
      margin: auto;
      display: flex;
      justify-content: center;
      align-items: center;
    }
  </style>
  <body>
  
  <!-- Modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Employee Document</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('checkdocument')}}">
           <div class="modal-body">
                  {{-- {{$_SERVER['SERVER_NAME']."/storage/uploads/documentUpload/".$document->document}} --}}
                  <iframe src="{{$_SERVER['SERVER_NAME']."/storage/uploads/documentUpload/".$document->document}}" frameborder="0" height="600px" width="800px" id="iframe"></iframe>
                  <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" value="1" name="term_and_condition" id="flexCheckChecked">
                    <label class="form-check-label" for="flexCheckChecked">
                     I have read this document & accept the conditions
                    </label>
                  </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
      </div>
    </div>
  </div>

    <script>
        
        var exampleModal = document.getElementById("exampleModal");
        exampleModal.classList.add("show")
        exampleModal.style.display = "block";
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

  </body>
</html>


{{-- <script>
    var modal = document.getElementById("myModal");
    var close = document.getElementsByClassName("close");

    function showModal() {
       modal.style.display = "block";
    }
    
    close.onclick = function () {
     modal.style.display = "none";
    };
</script> --}}