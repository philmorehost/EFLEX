<?php
// templates/dashboard_cards.php
?>
<div class="container-fluid">
    <h1 class="mb-4">Dashboard</h1>

    <div class="row">
        <!-- Card 1: Assigned Tests -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                Assigned Tests</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">5</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-black stretched-link" href="#">View Details</a>
                    <div class="small text-black"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <!-- Card 2: Completed Tests -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Completed Tests</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">3</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                 <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-black stretched-link" href="#">View Details</a>
                    <div class="small text-black"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <!-- Card 3: Average Score -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Average Score
                            </div>
                            <div class="row g-0 align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 fw-bold text-gray-800">85%</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                 <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-black stretched-link" href="#">View Details</a>
                    <div class="small text-black"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Placeholder for future charts or tables -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Performance Overview</h6>
                </div>
                <div class="card-body text-center">
                    <p>A chart displaying recent test scores will be implemented here using Chart.js.</p>
                    <i class="fas fa-chart-line fa-4x text-gray-300 my-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom styles for the cards, inspired by SB Admin 2 */
.card .border-left-primary { border-left: .25rem solid #4e73df!important; }
.card .border-left-success { border-left: .25rem solid #1cc88a!important; }
.card .border-left-info { border-left: .25rem solid #36b9cc!important; }
.text-xs { font-size: .8rem; }
.text-gray-300 { color: #dddfeb!important; }
.text-gray-800 { color: #5a5c69!important; }
.shadow { box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15)!important; }
.card-footer {
    background-color: rgba(0,0,0,.03);
    border-top: 1px solid rgba(0,0,0,.125);
    font-size: 0.9rem;
}
.stretched-link::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1;
    pointer-events: auto;
    content: "";
    background-color: rgba(0,0,0,0);
}
a.small.text-black.stretched-link {
    text-decoration: none;
}
</style>
