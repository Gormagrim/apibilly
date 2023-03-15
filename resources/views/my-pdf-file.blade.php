<!DOCTYPE html>
<html>
<head>
    <title>Title From OnlineWebTutorBlog</title>
</head>
<body>
    <h1>Title: {{ $title }}</h1>
    <h3>Author: {{ $author }}</h3>
    <p>ut aliquip ex ea commodoconsequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
    cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidata.</p>
</body>
</html>

<div class="modal-body a4" id="printThisEstimate">
    <div class="row">
        <div class="col-4 estimateLogo"><img src="http://localhost/erprojectApi/public" {{ $estimateLogo }} alt=""></div>
        
        <div class="col-8 Bigdevis text-center">DEVIS</div>
    </div>
    <div class="row">
        <div class="col-4 myEstimateName">
        </div>
    </div>
    <div class="row">
        <div class="col-4 myEstimateStreet">
        </div>
    </div>
    <div class="row">
        <div class="col-4 myEstimateCity">
        </div>
    </div>
    <div class="row">
        <div class="col-6 myEstimatePhone">
        </div>
        <div class="offset-1 col-5 destiCompName"></div>
    </div>
    <div class="row">
        <div class="col-6 myEstimateMail">
        </div>
        <div class="offset-1 col-5 destiName"></div>
    </div>
    <div class="row">
        <div class="offset-7 col-5 destiStreet"></div>
    </div>
    <div class="row">
        <div class="offset-7 col-5 destiCity"></div>
    </div>
    <div class="row">
        <div class="col-4 myEstimateNumber">
        </div>
    </div>
    <div class="row">
        <div class="col-4 myEstimateDate">
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-center myEstimateTitle">
        </div>
    </div>
    <div class="row mt-4 EstiTitle text-center">
        <div class="col-1 ref">Ref.</div>
        <div class="col-6 des">Désignation</div>
        <div class="col-1 qte">Quantité</div>
        <div class="col-2 pu">Prix Unitaire</div>
        <div class="col-2 pt">Prix Total</div>
    </div>
    <div class="row estiItems"></div>
    <div class="row totalItems text-center">
        <div class="offset-8 col-2 tht">Total HT en €</div>
        <div class="col-2 totalHtPrice"></div>
    </div>
    <div class="row notAuto text-center">
        <div class="offset-8 col-2 ptva">TVA en €</div>
        <div class="col-2 tvaPrice"></div>
        <div class="offset-8 col-2 tttc">Total TTC en €</div>
        <div class="col-2 totalTtcPrice"></div>
    </div>
    <div class="row">
        <div class="offset-8 col-4 autoEntreInfo text-center"></div>
    </div>
    <div class="row accord">
        <div class="col-12">
            En cas d’accord sur les termes du présent devis, merci de nous le retourner signé précédé de la mention : « BON POUR
            ACCORD »
        </div>
        <div class="offset-8 col-4">Date :</div>
        <div class="offset-8 col-4">Signature :</div>
    </div>
    <div class="row legalsInfos">
        <div class="col-12">
            Validité du devis : 1 mois <br />
            Condition de règlement : 50 % à la commande, solde à la livraison
            Toute somme non payée à sa date d’exigibilité produira de plein droit des intérêts de retard
            équivalents au triple du taux d’intérêts légal de l’année en cours ainsi que le paiement d’une somme
            de 40€ due au titre des frais de recouvrement.
        </div>
    </div>





</div>