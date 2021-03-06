import { Component,ViewChild,Output,NgZone } from '@angular/core';
import { StoryService} from '../../services/story.service';
import { NgForm } from '@angular/forms';
import {Router,ActivatedRoute} from '@angular/router';
import { FileUploadService } from '../../services/file-upload.service';
import { GlobalVariable } from '../../config';
import {AccordionModule,DropdownModule,SelectItem,CalendarModule,CheckboxModule} from 'primeng/primeng';
import { MentionService } from '../../services/mention.service';
import { AjaxService } from '../../ajax/ajax.service';
import {SummerNoteEditorService} from '../../services/summernote-editor.service';
import {SharedService} from '../../services/shared.service'; //added by Ryan
import { ProjectService } from '../../services/project.service';
declare var jQuery:any;    //Reference to Jquery
//declare const CKEDITOR;
//declare var tinymce:any;
declare var summernote:any;
let jsonForm={};//added by Ryan

 @Component({
    selector: 'story-form',
    templateUrl: 'story-form.html',
    styleUrls: ['story-form.css'],
    providers: [FileUploadService, StoryService,ProjectService]     

})

export class StoryComponent 
{
    @Output() public options = {
        readAs: 'ArrayBuffer'
      };
    public selectedTickets: string[] = [];
    public taskArray:any;
    public defaultTasksShow:boolean=true;
    public dragTimeout;
    public storyFormData=[];
    public storyData={};
    public form={};
    //CkEditor Configuration Options
    public toolbar={toolbar : [
    [ 'Heading 1', '-', 'Bold','-', 'Italic','-','Underline','Link','NumberedList','BulletedList' ]
    ],removePlugins:'elementspath',resize_enabled:true};
    public filesToUpload: Array<File>;
    public hasBaseDropZoneOver:boolean = false;
    public hasFileDroped:boolean = false;
    editorData:string='';
    public fileUploadStatus:boolean = false;
    public projectName;
    public checkvalue:boolean=false;
    public projectId:any;
    constructor(private projectService:ProjectService,private fileUploadService: FileUploadService, private _service: StoryService, private _router:Router,private mention:MentionService,private _ajaxService: AjaxService,private editor:SummerNoteEditorService,private shared:SharedService,private route:ActivatedRoute,private zone:NgZone) {
        this.filesToUpload = [];
    }

  
    ngOnInit() 
    {   
        jQuery('body').removeClass('modal-open'); 
        var thisObj = this;
  thisObj.route.queryParams.subscribe(
      params => 
      { 
      thisObj.route.params.subscribe(params => {
           thisObj.projectName=params['projectName'];
           thisObj.shared.change(this._router.url,thisObj.projectName,'Dashboard','New',thisObj.projectName);            thisObj.projectService.getProjectDetails(thisObj.projectName,(data)=>{
                if(data.statusCode ==200) {
                thisObj.projectId=data.data.PId;  
        thisObj._service.getStoryFields(thisObj.projectId,(response)=>
        {
            
            thisObj.taskArray=response.data.task_types;
              
              let DefaultValue;
               jsonForm['title'] ='';
               jsonForm['description'] ='';
               jsonForm['tasks']=this.selectedTickets;
               jsonForm['default_task']=[];
              if(response.statusCode==200)
              {
                  response.data.story_fields.forEach(element => {
                    var  item = element.Field_Name;
                    if(element.Type == 5){
                        element.DefaultValue=new Date().toLocaleString();
                    }else if(element.Type == 6){
                        DefaultValue=response.data.collaborators;
                    }else if(element.Type == 2){
                        DefaultValue=element.data; 
                    }           
                    jsonForm[item] = element.DefaultValue;
                    var priority=(element.Title=="Priority"?true:false);
                    var listItemArray: any;
                     listItemArray=thisObj.prepareItemArray(DefaultValue,priority,element.Title);
                    thisObj.storyFormData.push(
                       {'lable':element.Title,'model':element.Field_Name,'value':element.DefaultValue,'required':element.Required,'readOnly':element.ReadOnly,'type':element.Type,'values':listItemArray[0].filterValue,"labels":listItemArray}
                       )
                  });

           var preferences=response.data.task_preferences.PreferenceItems;
            if(preferences!=""){
                var preferences_array=preferences.split(',');
                for(let item of preferences_array)
                {
                        this.selectedTickets.push(item.trim());                
                }
                if(this.selectedTickets.length==this.taskArray.length)
                { 
                    this.checkvalue=true;
                }
               
                jsonForm['tasks']=this.selectedTickets;//shifted by Ryan from above


            }


              }else{
                    console.log("storyFrom Component ngOnInit fail---");
              }
        });
          jQuery("#title").keydown(function(e){
        if (e.keyCode == 13 && !e.shiftKey)
        {
            e.preventDefault();
        }
    }); 
 
        this.form = jsonForm;//shifted by Ryan from above

      
               
       }else{
       this._router.navigate(['project',this.projectName,'error']); 
       }
                
        });
        });
       
           })
 
       
    }

    /**
     * @author:Ryan Marshal
     * @description:This is used for initializing the summernote editor
     */
    ngAfterViewInit()
    {

        var formobj=this;
        this.editor.initialize_editor('summernote_create','keyup',formobj);
        jQuery(document)
    .one('focus.autoExpand', 'textarea.autoExpand', function(){
        var savedValue = this.value;
        this.value = '';
        this.baseScrollHeight = this.scrollHeight;
        this.value = savedValue;
    })
    .on('input.autoExpand', 'textarea.autoExpand', function(){
      var minRows = this.getAttribute('data-min-rows')|0, rows;
        this.rows = minRows;
        rows = Math.ceil((this.scrollHeight - this.baseScrollHeight) / 17);
        var newrows = Math.floor(this.scrollHeight/30);
        this.rows = newrows;
    });

    }

    /*
    @params    :  list,priority,status
    @ParamType :  any,boolean,string
    @Description: Preparing DropDown List.
    */
    public prepareItemArray(list:any,priority:boolean,status:string)
    {
      var listItem=[];
        var listMainArray=[];
      if(list.length>0)
      {
        for(var i=0;list.length>i;i++)
        {
            listItem.push({label:list[i].Name, value:list[i].Id,priority:priority,type:status});
        }
      }
       listMainArray.push({type:"",filterValue:listItem});
   
        return listMainArray;
    }

/*
---------------File Drag And Drop Methods *START*-----------------------
*/

    /*
    @params      : fileInput
    @ParamType   :  any
    @Description : Enabling the dropzone DIV on dragOver
    */
    
    public fileOverBase(fileInput:any):void 
    {
        this.hasBaseDropZoneOver = true;
        if(this.dragTimeout != undefined && this.dragTimeout != "undefined")
        { 
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
       if(comeFrom == 'fileChange') {
            this.filesToUpload = <Array<File>> fileInput.target.files;
       } else if(comeFrom == 'fileDrop') {
            this.filesToUpload = <Array<File>> fileInput.dataTransfer.files;
       } else {
            this.filesToUpload = <Array<File>> fileInput.target.files;
       }
            
            this.hasBaseDropZoneOver = false;
            this.fileUploadStatus = true;
            this.fileUploadService.makeFileRequest(GlobalVariable.FILE_UPLOAD_URL, [], this.filesToUpload).then(
                (result :Array<any>) => {
    
                for(var i = 0; i<result.length; i++){
                   result[i].originalname =  result[i].originalname.replace(/[^a-zA-Z0-9.]/g,'_'); 
                    var uploadedFileExtension = (result[i].originalname).split('.').pop();
                    if(uploadedFileExtension == "png" || uploadedFileExtension == "jpg" || uploadedFileExtension == "jpeg" || uploadedFileExtension == "gif") {
                        jQuery('#summernote_create').summernote('code',this.form['description']+"<p>[[image:" +result[i].path + "|" + result[i].originalname + "]]</p>" +" ");
                        this.form['description'] = jQuery('#summernote_create').summernote('code');
                    } else{
                        jQuery('#summernote_create').summernote('code',this.form['description']+"<p>[[file:" +result[i].path + "|" + result[i].originalname + "]]</p>" +" ");
                        this.form['description'] = jQuery('#summernote_create').summernote('code');
                    }
                }
                this.fileUploadStatus = false;
            }, (error) => {
                console.error("Error occured in story-formcomponent::fileUploadEvent"+error);
                this.form['description'] =jQuery('#summernote_create').summernote('code') + "Error while uploading";
                this.fileUploadStatus = false;
            });
    }
    

/*
------------File Upload Methods **END**--------------------
*/

    /*
    @Description:Creating Ticket/Story 

    */
    saveStory(){
        var thisObj = this;
        var editor=this.form['description'];
        var editorDesc=jQuery(editor).text().trim();
       if(editorDesc!='')
       { 
             this.form['default_task']=[];
                for (let task of this.form['tasks']) {
                for(let tsk of this.taskArray) {
                         if(tsk.Id == task) 
                       this.form['default_task'].push(tsk);
                 };
               }  
            this._service.saveStory(thisObj.projectId,thisObj.form,(response)=>{
                if(response.statusCode == 200){
                     thisObj._router.navigate(['project',thisObj.projectName,'list']);
                }
                    });
       }
       
    }

     /*
    @params    :  eventVal,whichDrop
    @ParamType :  any,string
    @Description: Based on filed:planlevel default Tasks Div will  show/hide.
    */
    dropChange(eventVal,whichDrop){
        if(whichDrop == "Plan Level"){
              if(eventVal==1){
                this.defaultTasksShow = true;
              }else{
                this.defaultTasksShow = false;
              }

        }
    }

    /**
     * @author:Ryan Marshal
     * @description:This is used for selecting/deselecting all Tasks
     */
    selectDeselectAll(checked:boolean)
    {
        console.log("==Status=="+checked);
        if(checked==true)
        {
            jsonForm['tasks']='';
            this.selectedTickets=[];
            for(let task of this.taskArray)
            {
                this.selectedTickets.push(task.Id);
            }
            
        }
        else
        {
            this.selectedTickets=[];
        }
        jsonForm['tasks']=this.selectedTickets;
    }

     /**
     * @author:Ryan Marshal
     * @description:This is used for checking Tasks Length and check/uncheck "All Tasks"
     */
    checkTasks(checked:boolean)
    {
        console.log("in check tasks"+JSON.stringify(jsonForm['tasks']));
        if(jsonForm['tasks'].length<this.taskArray.length && jsonForm['tasks']!=[])
        {
            console.log("in if"+this.checkvalue);
            this.checkvalue=false;
        }
        else
        {
            this.checkvalue=true;
        }
    }


    /** Only for Testing **/

    public sendMail()
    {
        var recepients={
            emaillist:['ryan_marshal@hotmail.com','kishore.neelam@techo2.com']
        }
        this._ajaxService.AjaxSubscribe('story/send-mail',recepients,(data)=>
        {

        })
    }     


}
