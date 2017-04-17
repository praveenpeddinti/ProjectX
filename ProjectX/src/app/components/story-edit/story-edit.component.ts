import { Component, OnInit,ViewChild } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http } from '@angular/http';
import { AjaxService } from '../../ajax/ajax.service';
import { StoryService} from '../../services/story.service';
import {NgForm} from '@angular/forms';
import {CalendarModule,AutoComplete} from 'primeng/primeng'; 	
import { FileUploadService } from '../../services/file-upload.service';
import { MentionService } from '../../services/mention.service';
import { GlobalVariable } from '../../config';
import {SummerNoteEditorService} from '../../services/summernote-editor.service';
import * as io from 'socket.io-client';

declare var jQuery:any; //reference to jquery
declare var summernote:any; //reference to summernote

@Component({
  selector: 'app-story-edit',
  templateUrl: './story-edit.component.html',
  styleUrls: ['./story-edit.component.css'],
  providers:[StoryService],
})

export class StoryEditComponent implements OnInit 
{
  
  @ViewChild('editor')  txt_area:any; //reference to CKEDITOR
  public dragTimeout;
  public minDate:Date;
  public datevalue:Date;
  private ticketData:any=[];
  private ticketid;
  private url_TicketId;
  private description;
  public form={};
  private fieldsData = [];
  private showMyEditableField =[];
  public dragdrop={extraPlugins:'dragdrop'};
  public taskArray:any;
  public taskIds=[];
  //ckeditor configuration options
  public toolbar={toolbar : [
      [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList']
  ],removePlugins:'elementspath,magicline',resize_enabled:true,};
  public filesToUpload: Array<File>;
  public hasBaseDropZoneOver:boolean = false;
  public hasFileDroped:boolean = false;
  public fileUploadStatus:boolean = false;
  public defaultTasksShow:boolean=true;

  constructor(private fileUploadService: FileUploadService, private _ajaxService: AjaxService,private _service: StoryService,
    public _router: Router,private mention:MentionService,
    private http: Http,private route: ActivatedRoute,private editor:SummerNoteEditorService) { 
           this.filesToUpload = [];
    }


  ngOnInit() 
  { var thisObj=this;
    //getting the route param(Ticketid) for specific ticket to edit
    this.route.params.subscribe(params => {
            this.url_TicketId = params['id'];
        });

      this._ajaxService.AjaxSubscribe("story/edit-ticket",{ticketId:this.url_TicketId},(data)=>
        {   
  
          data.data.ticket_details.Fields.filter (function(obj){
              if(obj.field_name=='planlevel' && obj.value==2){
               thisObj.defaultTasksShow= false;
              }
            });
           this.taskArray=data.data.task_types;
           var subtasks = data.data.ticket_details.Tasks;
            this.taskIds=[];
          for(let st of subtasks)
           this.taskIds.push(st.TaskType)
            for (let task of this.taskArray) {
              if(this.taskIds.some(x=>x==task.Id)){
                task.IsDefault=task.Id;
                task.disabled = true;
               }else{
                task.IsDefault=task.Id;
                task.disabled = null; 
               }
            }
             this.ticketData = data.data.ticket_details;
             this.description= this.ticketData.CrudeDescription;
             this.fieldsData = this.fieldsDataBuilder(this.ticketData.Fields,this.ticketData.TicketId);      
             jQuery("#description").summernote('code',this.description);      

        });
    
    this.minDate=new Date(); //set current date to datepicker as min date
  }

  ngAfterViewInit() 
  {
   
    this.editor.initialize_editor('description',null,null);      
  }

  /*
    @params    :  fieldsArray,ticketId
    @ParamType :  array,int
    @Description: Dynamic Ticket/Story Fields Rendering
    */
  fieldsDataBuilder(fieldsArray,ticketId)
  {
    let fieldsBuilt = [];
    let data = {title:"",value:"",readonly:true,required:true,id:"",fieldDataId:"",fieldName:"",fieldType:"",renderType:"",type:"",listdata:[]};
    for(let field of fieldsArray)
    {
      if(field.field_name != "customfield_2")
      {
          data = {title:"",value:"",readonly:true,required:true,id:"",fieldDataId:"",fieldName:"",fieldType:"",renderType:"",type:"",listdata:[]};
          switch(field.field_type)
          {
            case "Text":
            case "TextArea":
            data.title = field.title;
            data.value = field.value;
            // data.renderType = "input";
            data.renderType = (field.field_type == "TextArea")?"textarea":"input";
            data.type="text";
            break;
            case "List":
            data.title = field.title;
            data.value = field.readable_value.Name;
            data.renderType = "select";
            data.listdata = field.meta_data;
            data.fieldDataId = field.readable_value.Id;
            break;
            case "Numeric":
            data.title = field.title;
            data.value = field.value;
            data.renderType = "input";
            data.type="text";
            break;
            case "Date":
            data.title = field.title;
            data.value = field.readable_value;
            data.renderType = "date";
            data.type="date";
            break;
            case "DateTime":
            data.title = field.title;
            data.value = field.readable_value;
            data.renderType = "date";
            data.type="datetime";
            break;
            case "Team List":
            data.title = field.title;
            data.value = field.readable_value.UserName;
            data.renderType = "select";
            data.listdata = this.ticketData.collaborators;
            data.fieldDataId = field.readable_value.CollaboratorId;
            break;
            case "Bucket":
            data.title = field.title;
            data.value = field.readable_value.Name;
            data.renderType = "select";
            data.listdata = field.meta_data;
            data.fieldDataId = field.readable_value.Id;
            break;
            }

          data.readonly = (field.readonly == 1)?true:false;
          data.required = (field.required == 1)?true:false;
          data.id =  ticketId+"_"+field.field_name;
          data.fieldType = field.field_type;
          data.fieldName =  field.field_name;
          // if(field.field_name == "dod")
          // {
          //     data.renderType = "textarea";
          // }
          var priority=(data.title=="Priority"?true:false);
          var status=data.title;
          data.listdata=this.prepareItemArray(data.listdata,priority,status);
          fieldsBuilt.push(data);
          this.showMyEditableField.push((field.readonly == 1)?false:true);
      }
    }

    return fieldsBuilt;
  }

  /*
    @params    :  list,priority,status
    @ParamType :  any,boolean,string
    @Description: Building Dynamic Dropdown List Values
    */
 public prepareItemArray(list:any,priority:boolean,status)
 {
     var listItem=[];
     if(list.length>0)
     { 
       if(status == "Assigned to" || status == "Stake Holder")
       {
         listItem.push({label:"--Select a Member--", value:"",priority:priority,type:status});
       }        
       for(var i=0;list.length>i;i++)
       {
          listItem.push({label:list[i].Name, value:list[i].Id,priority:priority,type:status});
       }
     }
    return listItem;
 }

  /*
    @params    :  edit_data
    @ParamType :  Object
    @Description: Submit Edit Story/Ticket
    */
  editStorySubmit(edit_data)
  {  
    jQuery("#title_error").hide();
    jQuery("#desc_error").hide();
    var desc=jQuery("#description").summernote('code');
    desc=jQuery(desc).text().trim();
    var error = 0;
    if(edit_data.title=='')
    {
      jQuery("#title_error").show();
       error=1;
    }
    if(desc=='')
    {
      jQuery("#desc_error").show();
       error=1;
    }
    if(error == 0)
    {
      edit_data.description= jQuery("#description").summernote('code');
      edit_data.default_task=[];
      if(this.defaultTasksShow){
      var selectedTask=[];
            for (let task of this.taskArray) {
              if(this.taskIds.some(x=>x==task.Id) && !task.disabled){
               edit_data.default_task.push(task);
               }
            }
      }
       var post_data={
          'data':edit_data,
         
        }
        this._ajaxService.AjaxSubscribe("story/update-ticket-details",post_data,(data)=>
    
        {
         this._router.navigate(['story-detail',this.url_TicketId]);
        });
    }
          
   }

  /*
    @params    :  event,id
    @ParamType :  eventobj,string
    @Description: Check Empty Title or Description
    */
  checkEmpty(event,id)
  {
    if(event!="" || event!=null)
    {
      var idAttr = id;
      if(idAttr=='title')
      {
        jQuery("#title_error").hide();
      }
      if(idAttr=='description')
      {
        jQuery("#desc_error").hide();
      }
    }
  }

    /*
    @params      : fileInput
    @ParamType   :  any
    @Description : Enabling the dropzone DIV on dragOver
    */
  public fileOverBase(fileInput:any):void 
  {
      this.hasBaseDropZoneOver = true;
      if(this.dragTimeout != undefined && this.dragTimeout != "undefined"){ console.log("clear---");
      clearTimeout(this.dragTimeout);
      }
  
  }

   /*
    @params      : fileInput
    @ParamType   :  any
    @Description : Disabling the dropzone DIV on dragOver
    */
  public fileDragLeave(fileInput: any){
  var thisObj = this;
      if(this.dragTimeout != undefined && this.dragTimeout != "undefined"){
      clearTimeout(this.dragTimeout);
      }
       this.dragTimeout = setTimeout(function(){
       thisObj.hasBaseDropZoneOver = false;
      },500);
      
  }

    /*
    @params       : fileInput,comeFrom
    @ParamType    :  any,string
    @Description  : Uploading File
    */
  public fileUploadEvent(fileInput: any, comeFrom: string):void 
  {
     if(comeFrom == 'fileChange'){
          this.filesToUpload = <Array<File>> fileInput.target.files;
     } else if(comeFrom == 'fileDrop'){
          this.filesToUpload = <Array<File>> fileInput.dataTransfer.files;
     } else{
          this.filesToUpload = <Array<File>> fileInput.target.files;
     }

        this.hasBaseDropZoneOver = false;
        this.fileUploadStatus = true;
        this.fileUploadService.makeFileRequest(GlobalVariable.FILE_UPLOAD_URL, [], this.filesToUpload).then(
          (result :Array<any>) => {
            for(var i = 0; i<result.length; i++){
                var uploadedFileExtension = (result[i].originalname).split('.').pop();
                result[i].originalname =  result[i].originalname.replace(/[^a-zA-Z0-9.]/g,'_'); 
                if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                    this.description = jQuery("#description").summernote('code') + "<p>[[image:" +result[i].path + "|" + result[i].originalname + "]]</p>";
                    jQuery("#description").summernote('code',this.description);
                } else{
                    this.description = jQuery("#description").summernote('code') + "<p>[[file:" +result[i].path + "|" + result[i].originalname + "]]</p>";
                    jQuery("#description").summernote('code',this.description);
                }
            }
            this.fileUploadStatus = false;
        }, (error) => {
            this.description = jQuery("#description").summernote('code')+ "Error while uploading";
            this.fileUploadStatus = false;
        });
   }

     /*
     @Description  : Routing to Story Detail on Cancel Edit
     */
    cancelDesc()
    {
      this._router.navigate(['story-detail',this.url_TicketId]);
    }

    /**
     * @author:Ryan
     * @description: Send Mail to Assigned User/StakeHolder
     */
    sendMail(field_name,collaborator_id)
    {
      console.log("--Field Name--"+field_name);
      if(field_name=='assignedto' || field_name=='stakeholder')
      {
        console.log("--Collaborator Id--"+collaborator_id);
        var post_data=
        {
          'ticketId':this.url_TicketId,
          'collaborator':collaborator_id
        };
        this._ajaxService.AjaxSubscribe("story/send-mail",post_data,(data)=>
        {
         
        });
      }
    }

    //     /*For Notifications */
        
    //     //  var socket=io("http://10.10.73.39:4201");
    //     // //  var data={'ticketId':this.url_TicketId,'collaborator':collaborator_id};
    //     //         socket.emit('assignedTo',collaborator_id);
    //     var notify_data={'ticketId':this.url_TicketId,'comment_type':'assigned','collaborator':collaborator_id};

    //     this._ajaxService.NodeSubscribe('/assignedTo',notify_data,(data)=>
    //     {

    //     });
                
    //   }
    // }

}
