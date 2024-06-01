<p>Dear {{$admin->name}}</p>

<p>
    We received a request to reset your password for account assosiated with {{$admin->email}}.
    we can reset the password by clicking the button below :
    <br>
    <a href="{{$actionLink}}" target="_blank" style="color: #fff; border-color:#22bc66; border-style:solid; border-width:5px 10px; background-color:#22bc66; display:inline-block; text-decoration:none; box-shadow:0px 2px 3px rgba(0,0,0,0.16)" >Reset Password</a>
    <br>
    <b>NB:</b>This Link will be valid within 15 mins
    <br>
    if you didn't request for reset password, ignore this link
</p>
