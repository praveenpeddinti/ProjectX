import { Component } from '@angular/core';
import { NavController, NavParams, ViewController, AlertController } from 'ionic-angular';
import { Storage } from "@ionic/storage";
import { LoginPage } from '../login/login';
import { Globalservice } from '../../providers/globalservice';
import {Constants} from '../../providers/constants';
/*
  Generated class for the Popover page.

  See http://ionicframework.com/docs/v2/components/#navigation for more info on
  Ionic pages and navigation.
*/
@Component({
  selector: 'page-popover',
  templateUrl: 'popover.html',
  providers: [Globalservice, Constants]
})
export class PopoverPage {

 userName: string='';
 login: {username?: string, password?: string,token?:any} = {};
// userInfo = {"Id":"","username":"","token":"","projectId":1}
logoutParams = {"userInfo":{"Id":"","username":"","token":""},"projectId":1}

  constructor(private globalService: Globalservice,
            private constants: Constants,
            public navCtrl: NavController,
            public alertController: AlertController,
            private storage:Storage,
            public navParams: NavParams, 
            public viewCtrl: ViewController) {

           this.storage.get('userCredentials').then((value) => {
            console.log("in did load " + value.username);
            this.logoutParams.userInfo.Id= value.Id;
            this.logoutParams.userInfo.username=value.username;
            this.logoutParams.userInfo.token=value.token;
        });
     
            }
  close() {
    this.viewCtrl.dismiss();
  }
  ionViewDidLoad() {
    console.log('ionViewDidLoad PopoverPage');
      this.userName = this.navParams.get("username");
      console.log("User name is " + this.userName);
  }
logoutApp() {
  this.globalService.getLogout(this.constants.LogutUrl,this.logoutParams).subscribe(
        data =>{
             this.storage.remove('userCredentials').then( ()=>{
                   this.viewCtrl.dismiss();
                   //this.navCtrl.push(LoginPage);
                   this.navCtrl.setRoot(LoginPage)
                 });   
            },
        error=>{ 
            console.log("the error " + JSON.stringify(error)); 
            },
        ()=> console.log('logout api call complete'));
  }

}