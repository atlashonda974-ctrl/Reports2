@extends('master')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <div class="card">
                <div class="card-header border-0 pb-0 px-2 px-sm-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center w-100">
                        <h4 style="font-family: 'Reddit Sans'; font-weight: 600; letter-spacing: 1px;" 
                            class="card-title mb-2 mb-sm-0 fs-5 fs-sm-4">
                            Create Attendance Record
                        </h4>
                        <div class="d-flex gap-2 mt-2 mt-sm-0">
                            <a href="{{ route('attreq.index') }}" class="btn btn-outline-secondary btn-sm">
                                Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-4">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4">
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('attreq.store') }}" id="createForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="empcode" class="form-label fw-semibold">
                                    Employee Code <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg @error('empcode') is-invalid @enderror" 
                                       id="empcode" name="empcode" 
                                       value="{{ old('empcode') }}" 
                                       placeholder="Enter employee code"
                                       required
                                       style="font-size: 14px; height: 45px;">
                                @error('empcode')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Enter unique employee identification code</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="schddate" class="form-label fw-semibold">
                                    Schedule Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control form-control-lg @error('schddate') is-invalid @enderror" 
                                       id="schddate" name="schddate" 
                                       value="{{ old('schddate') }}" 
                                       required
                                       style="font-size: 14px; height: 45px;">
                                @error('schddate')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Select the attendance date</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="att" class="form-label fw-semibold">
                                    Attendance Type <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg @error('att') is-invalid @enderror" 
                                        id="att" name="att" 
                                        required
                                        style="font-size: 14px; height: 45px;">
                                    <option value="">-- Select Type --</option>
                                    @foreach($attendanceTypes as $type)
                                        <option value="{{ $type }}" 
                                            {{ old('att') == $type ? 'selected' : '' }}
                                            data-color="{{ [
                                                'Manual' => 'primary',
                                                'Official Visit' => 'info',
                                                'Travel' => 'success',
                                                'Training' => 'warning'
                                            ][$type] ?? 'secondary' }}">
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('att')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Select attendance type</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    Created By
                                </label>
                                <div class="border rounded p-3 bg-light" style="height: 45px; display: flex; align-items: center;">
                                    <span class="text-muted">System</span>
                                </div>
                                <small class="text-muted">Current user</small>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="remarks" class="form-label fw-semibold">
                                    Remarks
                                </label>
                                <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                          id="remarks" name="remarks" 
                                          rows="3"
                                          placeholder="Enter any remarks or notes..."
                                          style="font-size: 14px;">{{ old('remarks') }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Optional: Add any additional notes</small>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-text">
                                Fields marked with <span class="text-danger">*</span> are required
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary px-4" id="resetBtn">
                                    Reset
                                </button>
                                <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                                    Save Record
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .container-fluid {
        min-height: calc(100vh - 150px);
        display: flex;
        align-items: center;
        padding: 20px 0;
    }
    
    .card {
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        border: 1px solid #e3e6f0;
    }
    
    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 2px solid #dee2e6;
        border-radius: 12px 12px 0 0 !important;
        padding: 1rem 1.5rem;
    }
    
    .form-label {
        font-size: 14px;
        margin-bottom: 8px;
        color: #495057;
    }
    
    .form-control-lg, .form-select-lg {
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: all 0.3s;
    }
    
    .form-control-lg:focus, .form-select-lg:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        transform: translateY(-1px);
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
        border-color: #e9ecef !important;
    }
    
    textarea.form-control {
        border-radius: 8px;
        resize: vertical;
        min-height: 100px;
    }
    
    .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 10px 20px;
        transition: all 0.3s;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
    }
    
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: white;
    }
    
    hr {
        opacity: 0.2;
    }
    
    .form-text {
        font-size: 13px;
        color: #6c757d;
    }
    
   
    option[data-color="primary"] { color: #007bff; font-weight: 500; }
    option[data-color="info"] { color: #17a2b8; font-weight: 500; }
    option[data-color="success"] { color: #28a745; font-weight: 500; }
    option[data-color="warning"] { color: #ffc107; font-weight: 500; }
    
 
    .border-primary { border-color: #007bff !important; }
    .border-info { border-color: #17a2b8 !important; }
    .border-success { border-color: #28a745 !important; }
    .border-warning { border-color: #ffc107 !important; }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .col-lg-8 {
            max-width: 95%;
        }
        
        .card-header .d-flex {
            flex-direction: column;
            align-items: start !important;
        }
        
        .card-header .d-flex.gap-2 {
            width: 100%;
            margin-top: 10px;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 15px;
        }
        
        .d-flex.gap-2 {
            width: 100%;
            justify-content: stretch;
        }
        
        .d-flex.gap-2 .btn {
            flex: 1;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('#createForm').on('submit', function(e) {
        let valid = true;
        
        // Clear previous error states
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validate empcode
        if (!$('#empcode').val().trim()) {
            $('#empcode').addClass('is-invalid');
            $('#empcode').after('<div class="invalid-feedback d-block">Employee code is required</div>');
            valid = false;
        }
        
        // Validate date
        if (!$('#schddate').val()) {
            $('#schddate').addClass('is-invalid');
            $('#schddate').after('<div class="invalid-feedback d-block">Schedule date is required</div>');
            valid = false;
        }
        
        // Validate attendance type
        if (!$('#att').val()) {
            $('#att').addClass('is-invalid');
            $('#att').after('<div class="invalid-feedback d-block">Attendance type is required</div>');
            valid = false;
        }
        
        if (!valid) {
            e.preventDefault();
           
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
        } else {
            
            $('#submitBtn').prop('disabled', true).html('Saving...');
        }
    });
    
   
    $('#resetBtn').click(function() {
        $('#createForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#att').removeClass('border-primary border-info border-success border-warning');
    });
    
 
    $('#empcode, #schddate, #att').on('input change', function() {
        if ($(this).val().trim()) {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });
    
   
    $('#att').on('change', function() {
        const selected = $(this).find('option:selected');
        const color = selected.data('color');
        if (color) {
          
            $(this).removeClass('border-primary border-info border-success border-warning');
           
            $(this).addClass('border-' + color);
        }
    });
});
</script>
@endpush