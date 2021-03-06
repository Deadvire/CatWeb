<?php
require_once './php/credentialFunctions.php';
$data = 'Incorrect/missing ID and pwdKey values';
if(isset($_SESSION['ID']) && is_int($_SESSION['ID']) && isset($_SESSION['pwdKey']) && is_string($_SESSION['pwdKey']))
	$data = getInfo(null, $_SESSION['ID'], $_SESSION['pwdKey']);
?>
<!-- The section of the profile page -->
<section class="profilepage-section">
    <div class="container-fluid">
        <!-- The image and username of the user -->
        <div class="profile d-flex justify-content-center align-items-center flex-column mt-3">
            <img class="rounded-circle" src="img/dumbell.jpg" alt="Profile Pic">

            <h5 class="mt-2"><?php if(is_array($data) && is_string($data['username'])) echo $data['username']; else echo 'Username';?></h5>
        </div>
        <!-- Bottom of the page -->
        <div class="row mt-3">
            <div class="col-4">
                <!-- The left side of the page -->
                <div class="profileInformation">
                    <div class="title d-flex flex-column align-items-center">
                        <h2 class="fw-bold text-center">Profiel Informatie</h2>
                        <hr class="mt-1">
                    </div>
                    <form class="d-flex flex-column m-auto" method=POST class="credentials fs-5">
                        <input type=hidden name=formID value=updateUser />
                        <input type=hidden name=page value=user />
                        <!-- First Name -->
                        <label class="row item-1 justify-content-between">
                            <div class="col-6">
                                <p class="fw-bold">Voornaam</p>
                            </div>
                            <div class="col-6 text-center">
                                <input class="inputs" type=text name=FirstName autocomplete=given-name pattern="\w*"<?php if(is_array($data) && is_string($data['FirstName'])) echo ' value="', $data['FirstName'], '" ';?>>
                            </div>
                        </label>
                        <!-- Last Name -->
                        <label class="row item-1 justify-content-between">
                            <div class="col-6">
                                <p class="fw-bold">Achternaam</p>
                            </div>
                            <div class="col-6 text-center">
                                <input class="inputs" type=text name=LastName autocomplete=family-name pattern="\w*"<?php if(is_array($data) && is_string($data['LastName'])) echo ' value="', $data['LastName'], '" ';?>>
                            </div>
                        </label>
                        <!-- Email -->
                        <label class="row item-1 justify-content-between" title="Changing password or email requires password field to be filled.">
                            <div class="col-6">
                                <p class="fw-bold">Email</p>
                            </div>
                            <div class="col-6 text-center">
                                <input class="inputs" type=email autocomplete=email name=email<?php if(is_array($data) && is_string($data['email'])) echo ' value="', $data['email'], '" ';?>>
                            </div>
                        </label>
                        <!-- Password -->
                        <label class="row item-1 justify-content-between">
                            <div class="col-6">
                                <p class="fw-bold">Nieuw wachtwoord</p>
                            </div>
                            <div class="col-6 text-center">
                                <input class="inputs" type=password autocomplete=new-password pattern="[^\0\n\f\r\t\v]*" name=pwd_new>
                            </div>
                        </label>
                        <!-- Password -->
                        <label class="row item-1 justify-content-between">
                            <div class="col-6">
                                <p class="fw-bold">Wachtwoord</p>
                            </div>
                            <div class="col-6 text-center">
                                <input class="inputs" type=password autocomplete=new-password pattern="[^\0\n\f\r\t\v]+" name=pwd_old required>
                            </div>
                        </label>
                        <!-- Button to change the information about the user -->
                        <button type=submit class="btn btn-primary d-flex m-auto mt-3">Verander uw gegevens</button>
                        <button type=reset class="btn btn-primary d-flex m-auto mt-3">Reset</button>
                    </form>
                </div>
            </div>
            <div class="col-4">
                <div class="column">
                    <!-- Saved schedule -->
                    <div class="title d-flex flex-column align-items-center">
                        <h2 class="text-center fw-bold">Opgeslagen Schema</h2>
                        <hr class="mt-1" style="width: 50%;">
                    </div>
                    <!-- The content of the saved schedule section -->
                    <div class="content d-flex justify-content-center">
                        <a class="btn btn-primary m-auto mt-3" href="?page=savedSchema" style="color: white;">Zie alle opgeslagen Schema's</a>
                    </div>
                    <!-- DayOverview -->
                    <div class="week d-flex flex-column align-items-center mt-5">
                        <h2 class="text-center fw-bold">Dag -en Weekoverzicht</h2>
                        <hr class="mt-1" style="width: 50%;">
                        <!-- The content of the weekoverview section -->
                        <div class="content d-flex flex-column text-center"> 
                            <div class="title">
                                <h4 class="text-uppercase fw-bold" style="color: #5eb4a8;">Week 26</h4>
                            </div>
                            <div class="text d-flex gap-4">
                                <h5>1200 kg</h5>
                                <h5>800 cal</h5>
                            </div>
                            <div class="title mt-3">
                                <h4 class="text-uppercase fw-bold" style="color: #5eb4a8;">30 jun</h4>
                            </div>
                            <div class="text d-flex gap-4">
                                <h5>300 kg</h5>
                                <h5>200 cal</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <form id="js-filters" aria-label="Filters">
                    <!-- Right side of the page -->
                    <div class="profileFavourites">
                        <div class="title d-flex flex-column align-items-center">
                            <h2 class="text-center fw-bold">Favoriete Oefeningen</h2>
                            <hr class="mt-1">
                        </div>
                        <!-- The content of the favourites section -->
                        <section id="js-oefeningen"></section>
                    </div>  
                </form>
            </div>
        </div>
    </div>
</section>