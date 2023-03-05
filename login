<form id="loginform">
  <div class="single-form">
      <input type="text" class="form-control" placeholder="Username or email " required name="email">
  </div>
  <div class="single-form">
      <input type="password" class="form-control" placeholder="Password " required name="password">
  </div>
  <div class="single-form form-check">
      <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
      <label for="flexCheckDefault">Remember me</label>
  </div>
  <div class="mt-2">
      <div class="alert d-none alert-show alert-warning alert-dismissible fade show" role="alert">
        <strong class="type-text">Alert!</strong> <p class="msg-text"></p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
  </div>
  <div class="single-form">
      <button type="submit" id="loginbtn" class="btn btn-primary btn-hover-heading-color w-100">Login</button>
  </div>
  <div class="single-form">
      <p><a id="forgotpass" data-bs-toggle="modal" data-bs-target="#password" href="javascript:void(0)">Lost your password?</a></p>
  </div>
</form>



<div class="modal" id="password">
      <div class="modal-dialog ">
        <div class="modal-content">

          <!-- Modal Header -->
          <div class="modal-header">
            <h4 class="modal-title">Forgot Password</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <!-- Modal body -->
        <div class="modal-body">
         <div class=" justify-content-center align-items-center m-5">
            <div class="card" >
                  <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                       <div class="login-register-form">
                            <form id="forgot_pass">
                                <div class="single-form">
                                    <input type="text" class="form-control email" placeholder="Username or email " required name="email">
                                </div>
                                <div class="single-form otp d-none">
                                    <input type="text" class="form-control" placeholder="Enter Otp " required name="otp">
                                </div>
                                <div class="single-form otp d-none">
                                    <input type="text" class="form-control" placeholder="Enter New Password " required name="password">
                                </div>
                                <div class="mt-2">
                                    <div class="alert d-none alert-show alert-warning alert-dismissible fade show" role="alert">
                                      <strong class="type-text">Alert!</strong> <p class="msg-text"></p>
                                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                                <div class="single-form">
                                    <button type="submit" id="forbtn" class="btn btn-primary btn-hover-heading-color w-100">Send</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>        
          </div>
        </div>
          <!-- Modal footer -->
          <div class="modal-footer">
            <button type="button" class="btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
          </div>

        </div>
      </div>
    </div>
