<style>
/*
    DARK: #3f5728
    LIGHT: #a2cb47
*/
.paay {
    padding: 10px;
    text-align: center;
    border-radius: 5px;
    background-color: #fff;
}
.paay form {
    width: 95px;
    background: #fff;
    display: block;
    margin: auto;
}
.paay .paay-info {
    font-size: 18px;
    padding: 0px;
    margin: 10px;
    text-transform: uppercase;
}
.paay .paay-info.paay-green {
    background-color: #a2cb47;
    color: #fff;
    padding: 10px;
}
.paay .paay-button {
    border-radius: 5px;
    background-color: #a2cb47;
    background-image: url('<?php echo plugins_url('paay.png', __FILE__ ); ?>');
    background-repeat: no-repeat;
    background-position: center center;
    background-size: 90px;
    color: transparent;
    clear: both;
    overflow: hidden;
    border: 0px;
    width: 130px;
    height: 50px;
    margin: auto;
    cursor: pointer;
}

.paay .paay-button:hover {
    background-color: #a2cb47;
}

.paay-modal {
    position: fixed;
    top: 0px;
    left: 0px;
    display: block;
    width: 100%;
    height: 100%;
    z-index: 1000;
    background-color: rgba(0, 0, 0, 0.5);
}
.paay-modal .paay-box {
    margin: 10% auto;
    width: 50%;
    height: 440px;
    background: #a2cb47 !important;
    color: #fff;
    padding: 0px !important;
    -webkit-transition: width 0.5s, height 0.5s, -webkit-transform 0.5s;
    transition: width 0.5s, height 0.5s, transform 0.5s;
}
.paay-modal .paay-box.paay-initial {
    width: 10%;
    height: 10%;
}

.paay-modal .paay-box iframe {
    width: 100%;
    height: 100%;
    display: none;
}

/* Blaster loader */

.paay_blast_loader {
    margin: 20px auto;
    top: 50%;
    left: 50%;
    -webkit-transform: translate(-10%, -10%);
    -ms-transform: translate(-10%, -10%);
    transform: translate(-10%, -10%);
    width: 50px;
    height: 50px;
    background-color: #A2CB47;
    border-radius: 50%;
}
.paay_blast_loader:after {
    content: '';
    position: absolute;
    border-radius: 50%;
    top: 50%;
    left: 50%;
    border: 0px solid white;
    -webkit-transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
    -webkit-animation: loading 2200ms ease-out forwards infinite;
    animation: loading 2200ms ease-out forwards infinite;
}
@-webkit-keyframes loading {
    0% {
        border: 0px solid white;
    }
    20% {
        border: 8px solid white;
        width: 0%;
        height: 0%;
    }
    100% {
        border: 8px solid white;
        width: 100%;
        height: 100%;
    }
}

@keyframes loading {
    0% {
        border: 0px solid white;
    }
    20% {
        border: 8px solid white;
        width: 0%;
        height: 0%;
    }
    100% {
        border: 8px solid white;
        width: 100%;
        height: 100%;
    }
}
</style>