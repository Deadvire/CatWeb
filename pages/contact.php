<?php

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	if(isset($_POST['name']) && isset($_POST['email']) && isset($_POST['titel']) && isset($_POST['beschrijving'])){
		$string = "INSERT INTO site_mail (mail_name, mail_title, mail_description, mail_address) VALUES (' " . $_POST['name'] . "', '" . $_POST['titel'] . "', '" . $_POST['beschrijving'] ."', '" . $_POST['email'] ."')";
		DatbQuery(null, $string);
	}
}
?>

<!-- The section of the Contact page -->
<section class="contact-section">
	<div class="container">
		<!-- Title -->
		<h1 class="text-center">Contact</h1>
		<!-- Subtitle -->
		<p class="text-center pb-4">Hier kunt u informatie vinden over vragen die u heeft en gerelateerde vragen en antwoorden staan klaar voor u!</p>
		<div class="row justify-content-between">
			<!-- Left side of the page -->
			<div class="col-6">
				<h5>Formulier</h5>
				<hr class="w-25">
				<!-- Form -->
				<form method=post>
					<input type="hidden" name="page" value="contact">
					<div class="mb-3">
						<label for="formName" class="form-label">Name</label>
						<input type="name" class="form-control w-75" id="formName" name="name" required>
					</div>
					<div class="mb-3">
						<label for="formEmail" class="form-label">Email address</label>
						<input type="email" class="form-control w-75" id="formEmail" name="email" required>
					</div>
					<div class="mb-3">
						<label for="formTitel" class="form-label">Titel</label>
						<input type="titel" class="form-control w-75" id="formTitel" name="titel" required>
					</div>
					<div class="mb-3">
						<label for="formBeschrijving" class="form-label">Beschrijving</label>
						<textarea type="beschrijving" class="form-control" rows="5" id="formBeschrijving" name="beschrijving" required></textarea>
					</div>
					<button type="submit" class="btn btn-primary text-white w-75">Submit</button>
				</form>
			</div>
			<!-- Vertical line in between two columns -->
			<div class="col-1 text-center">
				<div class="vr h-100 mt-3"></div>
			</div>
			<!-- Right side of the page -->
			<div class="col-5">
				<h5>Veelgestelde vragen</h5>
				<hr class="w-75">
				<!-- Accordion -->
				<div class="accordion accordion-flush" id="accordionFlushExample">
					<div class="accordion-item">
						<h2 class="accordion-header" id="flush-headingOne">
						<button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
							Hoe kan ik mijn workouts opslaan?
						</button>
						</h2>
						<div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
							<div class="accordion-body">
								Om je workouts op te slaan moet u eerst een account aanmaken voordat u het kan opslaan.
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="flush-headingTwo">
						<button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
							Wat houdt Build Your Workout in?
						</button>
						</h2>
						<div id="flush-collapseTwo" class="accordion-collapse collapse" aria-labelledby="flush-headingTwo" data-bs-parent="#accordionFlushExample">
							<div class="accordion-body">
								Build Your Workout is een nieuwe functie door middel van een paar vragen te beantwoorden, krijgt u een gecalculeerde workout die speciaal voor u is gemaakt.
								<button class="btn btn-primary d-flex mt-3"><a class="text-white text-decoration-none" href="?page=build">Ga naar Build Your Workout</a></button>
							</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="flush-headingThree">
						<button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
							Hoe gebruik ik Schema?
						</button>
						</h2>
						<div id="flush-collapseThree" class="accordion-collapse collapse" aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample">
							<div class="accordion-body">
								Schema is een van de nieuwere functie op CatWeb, met deze functie kunt u een eigen schema maken (alleen als u ingelogd bent). Door middel van bereik tot alle oefeningen kan jij bepalen wat voor routine jij wilt doen.
								<button class="btn btn-primary d-flex mt-3"><a class="text-white text-decoration-none" href="?page=schema">Ga naar Schema</a></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>