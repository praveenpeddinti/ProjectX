import { Component } from '@angular/core';
import {IonicPage,NavController, NavParams, AlertController, ViewController, LoadingController, PopoverController, ModalController, Platform} from 'ionic-angular';
import {Storage} from "@ionic/storage";
import {LogoutPage} from '../logout/logout';
import {NotificationPage} from '../notification/notification';
import {Globalservice} from '../../providers/globalservice';
declare var jQuery: any;
declare var socket:any;
/**
 * Generated class for the HeaderPage page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */
@IonicPage()
@Component({
  selector: 'page-header',
  templateUrl: 'header.html',
})
export class HeaderPage {
public notify_count:any='';
public title:any;
public backbutton:any;
public logo:any;
public leftPannel:any
  constructor(private globalService: Globalservice,public navCtrl: NavController, public navParams: NavParams, public popoverCtrl: PopoverController) {
  var headerInfo=JSON.parse(localStorage.getItem("headerInfo"));
  this.title=headerInfo.title;
  this.backbutton=headerInfo.backbutton;
  this.logo =headerInfo.logo;
  this.leftPannel =headerInfo.leftPannel;
  var post_data = {};
            var thisObj = this;
            this.globalService.SocketSubscribe('getAllNotificationsCount', post_data);
            socket.on('getAllNotificationsCountResponse', function (data) {
                data = JSON.parse(data);
                console.log("getAllNotificationsCountResponse-------Mobile----" + data.count);
                thisObj.notify_count = data.count;
            });

 }

  ionViewDidLoad() {
  
    console.log('ionViewDidLoad HeaderPage');
  }
  public openPopover(myEvent) {  
        let popover = this.popoverCtrl.create(LogoutPage);
        popover.present({
            ev: myEvent
        });
    };

     /**
     * @author Anand Singh
     * @uses Goto All notifications
     */
    public gotoNotification(){
          this.navCtrl.push(NotificationPage);
        
        } 
}