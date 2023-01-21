<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email verified</title>
</head>
<style>
  .white-div {
    background: #2253743b;
    margin: auto;
    text-align: center;
    padding: 0 9px 111px 9px;
    border-radius: 10px;
    margin-top: 150px;
    width: 40%;
    box-shadow: 0px 0px 4px 0px #0006;
}
.white-div img {
    width: 300px !important;
    display: block;
    margin: auto;
}
span.thank {
    display: block;
    font-size: 33px;
    font-weight: 600;
    margin: 20px 0;
    color: #25526f;
    letter-spacing: 0px;
    text-transform: capitalize;
    font-family: serif;
}
span.mem {
    font-size: 20px;
    margin: 10px 0;
    display: block;
    color: #8a8da6;
    max-width: 500px;
    text-transform: capitalize;
    margin: auto;
    font-size: 14px;
}
span.thank.thank-2 {
    padding-left: 16px;
    /* margin-top: 24px; */
    padding-top: 45px;
    display: block;
    text-align: inherit;
    font-size: 22px;
}
@media only screen and (max-width: 991px)
{
    .white-div img {
    width: 200px;
    display: block;
    margin: auto;
}
.white-div {
    background: #2253743b;
    margin: auto;
    text-align: center;
    padding: 0 9px 111px 9px;
    border-radius: 10px;
    margin-top: 0px !important;
    margin: 20px auto;
    width: 69%;
    box-shadow: 0px 0px 4px 0px #0006;
}
}
@media only screen and (max-width: 767px)
{
    .white-div {
    width: 70%;
}
}
@media only screen and (max-width: 480px)
{
    .white-div {
    width: 88%;
}
}
</style>
<body >
    <div class="white-div">
        <span class="thank thank-2"> Hello </span>
        <img src="http://dbt.teb.mybluehostin.me/shoplift/we.png" alt="tick">
        <span class="thank">  Thanks for Signup </span>
        <span class="mem"> Your Code is {{$email_verified_code ?? ''}}</span>
    </div>
</body>
</html>