<!-- Remove duty modal -->
<div class="modal fade" id="removeDutyModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Rezygnuję z posługi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="remove-duty-form">
                    <div class="mb-4">
                        <label for="remove-duty-date" class="form-label">Data</label>
                        <input type="text" class="form-control" id="remove-duty-date" name="date" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="remove-duty-hour" class="form-label">Godzina</label>
                        <input type="text" class="form-control" id="remove-duty-hour" readonly>
                    </div>
                    <input type="hidden" id="remove-duty-duty-id">
                    <button type="submit" class="btn btn-primary">Zapisz</button>
                </form>
            </div>
        </div>
    </div>
</div>
