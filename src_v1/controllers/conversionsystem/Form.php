<form method="post" class="needs-validation" novalidate>
	<?=\SCDS\CSRF::write()?>
	<div class="mb-3">
		<label class="form-label" for="event">Select an Event</label>
		<div class="row mb-2">
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio1" name="event" value="50 Free" class="form-check-input" required>
				  <label class="form-check-label" for="customRadio1">50 Free</label>
				</div>
			</div>
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio2" name="event" value="100 Free" class="form-check-input">
				  <label class="form-check-label" for="customRadio2">100 Free</label>
				</div>
			</div>
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio3" name="event" value="200 Free" class="form-check-input">
				  <label class="form-check-label" for="customRadio3">200 Free</label>
				</div>
			</div>
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio4" name="event" value="400 Free" class="form-check-input">
				  <label class="form-check-label" for="customRadio4">400 Free</label>
				</div>
			</div>
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio5" name="event" value="800 Free" class="form-check-input">
				  <label class="form-check-label" for="customRadio5">800 Free</label>
				</div>
			</div>
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio6" name="event" value="1500 Free" class="form-check-input">
				  <label class="form-check-label" for="customRadio6">1500 Free</label>
				</div>
			</div>
		</div>
		<div class="row mb-2">
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio7" name="event" value="50 Breast" class="form-check-input">
				  <label class="form-check-label" for="customRadio7">50 Breast</label>
				</div>
			</div>
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio8" name="event" value="100 Breast" class="form-check-input">
				  <label class="form-check-label" for="customRadio8">100 Breast</label>
				</div>
			</div>
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio9" name="event" value="200 Breast" class="form-check-input">
				  <label class="form-check-label" for="customRadio9">200 Breast</label>
				</div>
			</div>
		</div>
		<div class="row mb-2">
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio10" name="event" value="50 Fly" class="form-check-input">
				  <label class="form-check-label" for="customRadio10">50 Fly</label>
				</div>
			</div>
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio11" name="event" value="100 Fly" class="form-check-input">
				  <label class="form-check-label" for="customRadio11">100 Fly</label>
				</div>
			</div>
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio12" name="event" value="200 Fly" class="form-check-input">
				  <label class="form-check-label" for="customRadio12">200 Fly</label>
				</div>
			</div>
		</div>
		<div class="row mb-2">
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio13" name="event" value="50 Back" class="form-check-input">
				  <label class="form-check-label" for="customRadio13">50 Back</label>
				</div>
			</div>
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio14" name="event" value="100 Back" class="form-check-input">
				  <label class="form-check-label" for="customRadio14">100 Back</label>
				</div>
			</div>
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio15" name="event" value="200 Back" class="form-check-input">
				  <label class="form-check-label" for="customRadio15">200 Back</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio16" name="event" value="200 IM" class="form-check-input">
				  <label class="form-check-label" for="customRadio16">200 IM</label>
				</div>
			</div>
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="customRadio17" name="event" value="400 IM" class="form-check-input">
				  <label class="form-check-label" for="customRadio17">400 IM</label>
				</div>
			</div>
		</div>
    <div class="invalid-feedback">
      Please choose an event.
    </div>
	</div>

	<div class="mb-3">
		<label class="form-label" for="source">Convert From</label>
		<div class="row">
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="sourcea" name="source" value="25m" class="form-check-input" checked required>
				  <label class="form-check-label" for="sourcea">25m Pool</label>
				</div>
			</div>
			<div class="col-6 col-md-2">
				<div class="form-check">
				  <input type="radio" id="sourceb" name="source" value="50m" class="form-check-input">
				  <label class="form-check-label" for="sourceb">50m Pool</label>
				</div>
			</div>
		</div>
    <div class="invalid-feedback">
      Please select the pool the event was swam in.
    </div>
	</div>

    <div class="row">
      <div class="col-md-8">
        <div class="mb-3">
        <label>Time</label>
          <div class="input-group">
            <input type="number" max="100" min="0" name="mins" pattern="[0-9]*" inputmode="numeric" class="form-control" placeholder="Minutes">
            <input type="number" max="59" min="0" name="secs" pattern="[0-9]*" inputmode="numeric" class="form-control" placeholder="Seconds">
            <input type="number" max="99" min="0" name="hunds" pattern="[0-9]*" inputmode="numeric" class="form-control" placeholder="Hundreds">
          </div>
          <div class="invalid-feedback">
            Please enter a valid time.
          </div>
        </div>
      </div>
    </div>

	<p class="mb-0">
		<button class="btn btn-success" type="submit">Convert</button>
	</p>
</form>